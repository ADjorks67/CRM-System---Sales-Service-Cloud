<section class="rounded bg-card p-4 shadow-[var(--shadow-card)]" aria-labelledby="hierarchy-heading">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h2 id="hierarchy-heading" class="text-base font-semibold text-primary">Account Hierarchy</h2>
        <a href="{{ route('accounts.hierarchy', $account) }}" class="text-sm text-secondary no-underline">View all accounts in hierarchy</a>
    </div>

    <dl class="mb-3 grid gap-2 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-text/60">Roll-up employees</dt>
            <dd class="font-medium">{{ number_format($hierarchyRollUp['employees'] ?? 0) }}</dd>
        </div>
        <div>
            <dt class="text-text/60">Roll-up annual revenue</dt>
            <dd class="font-medium">{{ number_format($hierarchyRollUp['annual_revenue'] ?? 0, 2) }}</dd>
        </div>
    </dl>

    @if ($hierarchyTree === null)
        <p class="text-sm text-text/70">No hierarchy to display.</p>
    @else
        <ul class="list-none" role="tree" aria-label="Account hierarchy tree">
            @include('accounts.partials.hierarchy-node', [
                'node' => $hierarchyTree,
                'current' => $account,
                'depth' => 0,
            ])
        </ul>
    @endif
</section>
