package com.example.model

enum class UserRole(val label: String) {
    PELANGGAN("Pelanggan"),
    MERCHANT("Merchant"),
    DRIVER("Driver"),
    ADMIN("Admin"),
    SUPERADMIN("Superadmin"),
}

enum class OrderStatus(val label: String) {
    MENUNGGU_KONFIRMASI("Menunggu Konfirmasi Merchant"),
    PESANAN_DIKONFIRMASI("Pesanan Dikonfirmasi"),
    SEDANG_DISIAPKAN("Sedang Disiapkan"),
    MENUNGGU_DRIVER("Menunggu Driver"),
    DRIVER_MENUJU_MERCHANT("Driver Menuju Merchant"),
    PESANAN_DIAMBIL("Pesanan Diambil"),
    DALAM_PENGIRIMAN("Dalam Pengiriman"),
    SELESAI("Selesai"),
    DIBATALKAN("Dibatalkan"),
}

data class District(
    val id: Int,
    val name: String,
    val postalCode: String,
    val baseDeliveryFee: Int,
)

data class Merchant(
    val id: Int,
    val storeName: String,
    val districtName: String,
    val category: String,
    val rating: Double,
    val isOpen: Boolean = true,
)

data class Product(
    val id: Int,
    val merchantId: Int,
    val merchantName: String,
    val name: String,
    val category: String,
    val price: Int,
    val promoPrice: Int?,
    val stock: Int,
    val description: String,
)

data class CartItem(
    val product: Product,
    val quantity: Int,
    val note: String = "",
) {
    val effectiveUnitPrice: Int get() = product.promoPrice ?: product.price
    val lineSubtotal: Int get() = effectiveUnitPrice * quantity
}

data class Order(
    val code: String,
    val merchantName: String,
    val customerName: String,
    val districtName: String,
    val address: String,
    val itemsSummary: String,
    val subtotal: Int,
    val deliveryFee: Int,
    val serviceFee: Int,
    val voucherDiscount: Int,
    val platformCommission: Int,
    val merchantNetIncome: Int,
    val driverNetIncome: Int,
    val totalAmount: Int,
    val paymentMethod: String,
    val status: OrderStatus,
    val verificationPin: String,
)
