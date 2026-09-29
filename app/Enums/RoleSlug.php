<?php

namespace App\Enums;

enum RoleSlug: string
{
    case SystemAdministrator = 'system_administrator';
    case SalesManager = 'sales_manager';
    case SalesRepresentative = 'sales_representative';
    case ServiceRepresentative = 'service_representative';
    case ReadOnlyUser = 'read_only_user';

    public function label(): string
    {
        return match ($this) {
            self::SystemAdministrator => 'System Administrator',
            self::SalesManager => 'Sales Manager',
            self::SalesRepresentative => 'Sales Representative',
            self::ServiceRepresentative => 'Service Representative',
            self::ReadOnlyUser => 'Read-Only User',
        };
    }
}
