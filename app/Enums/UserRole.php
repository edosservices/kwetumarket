<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case AdminManager = 'admin_manager';
    case CatalogManager = 'catalog_manager';
    case OrderManager = 'order_manager';
    case FinanceManager = 'finance_manager';
    case SupportAgent = 'support_agent';
    case MarketingManager = 'marketing_manager';
    case Moderator = 'moderator';
    case Vendor = 'vendor';
    case VendorManager = 'vendor_manager';
    case VendorCatalogManager = 'vendor_catalog_manager';
    case VendorOrderManager = 'vendor_order_manager';
    case DeliveryManager = 'delivery_manager';
    case DeliveryAgent = 'delivery_agent';
    case Customer = 'customer';

    public function label(): string
    {
        return __('ui.roles.'.$this->value);
    }

    /**
     * @return list<self>
     */
    public static function adminStaff(): array
    {
        return [
            self::AdminManager,
            self::CatalogManager,
            self::OrderManager,
            self::FinanceManager,
            self::SupportAgent,
            self::MarketingManager,
            self::Moderator,
        ];
    }

    /**
     * @return list<self>
     */
    public static function platform(): array
    {
        return [self::SuperAdmin, ...self::adminStaff()];
    }

    /**
     * @return list<self>
     */
    public static function vendorSide(): array
    {
        return [
            self::Vendor,
            self::VendorManager,
            self::VendorCatalogManager,
            self::VendorOrderManager,
        ];
    }

    /**
     * @return list<self>
     */
    public static function deliverySide(): array
    {
        return [self::DeliveryManager, self::DeliveryAgent];
    }
}
