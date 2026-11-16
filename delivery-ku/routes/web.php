<?php
declare(strict_types=1);

return [
    '' => [UserController::class, 'home'],
    'home' => [UserController::class, 'home'],

    // Auth
    'auth/login' => [AuthController::class, 'showLogin', AuthController::class, 'login'],
    'auth/register' => [AuthController::class, 'showRegister', AuthController::class, 'register'],
    'auth/forgot-password' => [AuthController::class, 'showForgotPassword', AuthController::class, 'forgotPassword'],
    'auth/logout' => [AuthController::class, 'logout'],

    // Superadmin
    'superadmin/dashboard' => [SuperadminController::class, 'dashboard'],
    'superadmin/users' => [SuperadminController::class, 'users', SuperadminController::class, 'users'],
    'superadmin/merchants' => [SuperadminController::class, 'merchants', SuperadminController::class, 'merchants'],
    'superadmin/drivers' => [SuperadminController::class, 'drivers', SuperadminController::class, 'drivers'],
    'superadmin/products' => [SuperadminController::class, 'categoriesAndProducts', SuperadminController::class, 'categoriesAndProducts'],
    'superadmin/orders' => [SuperadminController::class, 'ordersAndDeliveries', SuperadminController::class, 'ordersAndDeliveries'],
    'superadmin/payments' => [SuperadminController::class, 'paymentsAndCommissions', SuperadminController::class, 'paymentsAndCommissions'],
    'superadmin/vouchers' => [SuperadminController::class, 'vouchers', SuperadminController::class, 'vouchers'],
    'superadmin/regions' => [SuperadminController::class, 'regions', SuperadminController::class, 'regions'],
    'superadmin/banners' => [SuperadminController::class, 'bannersAndReviews', SuperadminController::class, 'bannersAndReviews'],
    'superadmin/reports' => [SuperadminController::class, 'reports'],
    'superadmin/settings' => [SuperadminController::class, 'settings', SuperadminController::class, 'settings'],
    'superadmin/logs' => [SuperadminController::class, 'logs'],
    'superadmin/backup' => [SuperadminController::class, 'backup'],
    'superadmin/profile' => [SuperadminController::class, 'profile', SuperadminController::class, 'profile'],

    // Admin
    'admin/dashboard' => [AdminController::class, 'dashboard'],
    'admin/verifications' => [AdminController::class, 'verifications', AdminController::class, 'verifications'],
    'admin/orders' => [AdminController::class, 'orders', AdminController::class, 'orders'],
    'admin/complaints' => [AdminController::class, 'complaints', AdminController::class, 'complaints'],
    'admin/reports' => [AdminController::class, 'reports'],

    // Merchant
    'merchant/dashboard' => [MerchantController::class, 'dashboard'],
    'merchant/profile' => [MerchantController::class, 'storeProfile', MerchantController::class, 'storeProfile'],
    'merchant/products' => [MerchantController::class, 'products', MerchantController::class, 'products'],
    'merchant/orders' => [MerchantController::class, 'orders', MerchantController::class, 'orders'],
    'merchant/finance' => [MerchantController::class, 'finance'],
    'merchant/reviews' => [MerchantController::class, 'reviews'],

    // Driver
    'driver/dashboard' => [DriverController::class, 'dashboard'],
    'driver/action' => [DriverController::class, 'actionOrder', DriverController::class, 'actionOrder'],
    'driver/deliveries' => [DriverController::class, 'deliveriesAndEarnings'],
    'driver/profile' => [DriverController::class, 'profile', DriverController::class, 'profile'],

    // User / Pelanggan
    'user/home' => [UserController::class, 'home'],
    'user/search' => [UserController::class, 'search'],
    'user/merchant' => [UserController::class, 'merchantDetail'],
    'user/cart' => [UserController::class, 'cart', UserController::class, 'cart'],
    'user/checkout' => [UserController::class, 'checkout', UserController::class, 'checkout'],
    'user/orders' => [UserController::class, 'orders'],
    'user/order-detail' => [UserController::class, 'orderDetail', UserController::class, 'orderDetail'],
    'user/favorites' => [UserController::class, 'favorites', UserController::class, 'favorites'],
    'user/addresses' => [UserController::class, 'addresses', UserController::class, 'addresses'],
    'user/profile' => [UserController::class, 'profile', UserController::class, 'profile'],
    'user/help' => [UserController::class, 'help', UserController::class, 'help'],

    // AJAX Polling API
    'api/order-status' => [ApiController::class, 'orderStatus'],
    'api/notifications' => [ApiController::class, 'notificationsUnread'],
    'api/merchant-pending' => [ApiController::class, 'merchantPendingCount'],
    'api/driver-available' => [ApiController::class, 'driverAvailableCount'],
    'api/villages' => [ApiController::class, 'villagesByDistrict'],
];
