<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AccountHierarchyService
{
    /**
     * @return Collection<int, Account>
     */
    public function ancestors(Account $account, ?User $user = null): Collection
    {
        $ancestors = collect();
        $current = $account->parentAccount;

        while ($current !== null) {
            if ($user !== null && ! $this->isVisible($current, $user)) {
                break;
            }

            $ancestors->push($current);
            $current = $current->parentAccount;
        }

        return $ancestors;
    }

    /**
     * Visible descendants of the account (excludes the root itself).
     *
     * @return Collection<int, Account>
     */
    public function descendants(Account $account, User $user): Collection
    {
        return $this->collectVisibleSubtree($account, $user)->skip(1)->values();
    }

    /**
     * Account plus all visible descendants (depth-first, root first).
     *
     * @return Collection<int, Account>
     */
    public function visibleSubtree(Account $account, User $user): Collection
    {
        if (! $this->isVisible($account, $user)) {
            return collect();
        }

        return $this->collectVisibleSubtree($account, $user);
    }

    /**
     * Nested tree for UI: each node is ['account' => Account, 'children' => list].
     *
     * @return array{account: Account, children: list<array{account: Account, children: list}>}|null
     */
    public function tree(Account $account, User $user): ?array
    {
        if (! $this->isVisible($account, $user)) {
            return null;
        }

        return $this->buildNode($account, $user);
    }

    /**
     * Walk visible parents to the highest ancestor the user can see.
     */
    public function visibleRoot(Account $account, User $user): Account
    {
        $root = $account;

        while ($root->parent_account_id !== null) {
            $parent = $root->parentAccount;

            if ($parent === null || ! $this->isVisible($parent, $user)) {
                break;
            }

            $root = $parent;
        }

        return $root;
    }

    /**
     * Full hierarchy subgraph: visible root and its visible descendants.
     *
     * @return array{account: Account, children: list<array{account: Account, children: list}>}|null
     */
    public function hierarchyTree(Account $account, User $user): ?array
    {
        return $this->tree($this->visibleRoot($account, $user), $user);
    }

    /**
     * @return array{employees: int, annual_revenue: float, account_count: int}
     */
    public function rollUp(Account $account, User $user): array
    {
        $subtree = $this->visibleSubtree($account, $user);

        return [
            'employees' => (int) $subtree->sum(fn (Account $a) => (int) ($a->employees ?? 0)),
            'annual_revenue' => (float) $subtree->sum(fn (Account $a) => (float) ($a->annual_revenue ?? 0)),
            'account_count' => $subtree->count(),
        ];
    }

    /**
     * True when proposed parent would create a cycle (self or descendant as parent).
     */
    public function wouldCreateCycle(Account $account, ?int $parentAccountId): bool
    {
        if ($parentAccountId === null) {
            return false;
        }

        if ((int) $parentAccountId === (int) $account->id) {
            return true;
        }

        $descendantIds = $this->allDescendantIds($account);

        return in_array((int) $parentAccountId, $descendantIds, true);
    }

    /**
     * @throws ValidationException
     */
    public function assertValidParent(?Account $account, ?int $parentAccountId): void
    {
        if ($parentAccountId === null) {
            return;
        }

        if ($account === null) {
            return;
        }

        if ($this->wouldCreateCycle($account, $parentAccountId)) {
            throw ValidationException::withMessages([
                'parent_account_id' => 'Parent account cannot be this account or one of its descendants.',
            ]);
        }
    }

    /**
     * @return list<int>
     */
    public function allDescendantIds(Account $account): array
    {
        $ids = [];
        $queue = [$account->id];

        while ($queue !== []) {
            $parentId = array_shift($queue);
            $childIds = Account::query()
                ->where('parent_account_id', $parentId)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($childIds as $childId) {
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }

    /**
     * @return Collection<int, Account>
     */
    private function collectVisibleSubtree(Account $account, User $user): Collection
    {
        $result = collect([$account]);
        $queue = [$account->id];

        while ($queue !== []) {
            $parentId = array_shift($queue);
            $children = Account::query()
                ->visibleTo($user)
                ->where('parent_account_id', $parentId)
                ->orderBy('name')
                ->get();

            foreach ($children as $child) {
                $result->push($child);
                $queue[] = $child->id;
            }
        }

        return $result;
    }

    /**
     * @return array{account: Account, children: list<array{account: Account, children: list}>}
     */
    private function buildNode(Account $account, User $user): array
    {
        $children = Account::query()
            ->visibleTo($user)
            ->where('parent_account_id', $account->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Account $child) => $this->buildNode($child, $user))
            ->values()
            ->all();

        return [
            'account' => $account,
            'children' => $children,
        ];
    }

    private function isVisible(Account $account, User $user): bool
    {
        return Account::query()->whereKey($account->id)->visibleTo($user)->exists();
    }
}
