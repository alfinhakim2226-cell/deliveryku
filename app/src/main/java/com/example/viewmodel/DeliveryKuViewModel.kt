package com.example.viewmodel

import androidx.lifecycle.ViewModel
import com.example.model.CartItem
import com.example.model.District
import com.example.model.Merchant
import com.example.model.Order
import com.example.model.OrderStatus
import com.example.model.Product
import com.example.model.UserRole
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import java.text.NumberFormat
import java.util.Locale

data class DeliveryKuUiState(
    val activeRole: UserRole = UserRole.PELANGGAN,
    val districts: List<District> = defaultDistricts(),
    val selectedDistrict: District = defaultDistricts().first(),
    val merchants: List<Merchant> = defaultMerchants(),
    val products: List<Product> = defaultProducts(),
    val searchQuery: String = "",
    val selectedCategory: String = "Semua",
    val cart: List<CartItem> = emptyList(),
    val voucherCode: String = "",
    val selectedPayment: String = "COD (Bayar di Tempat)",
    val deliveryAddress: String = "Jl. Mayor Dasuki No. 48, Jatibarang Baru, Indramayu",
    val orders: List<Order> = defaultOrders(),
    val merchantStoreOpen: Boolean = true,
    val driverOnline: Boolean = true,
    val commissionRatePercent: Int = 15,
    val serviceFee: Int = 2000,
    val snackbarMessage: String? = null,
)

class DeliveryKuViewModel : ViewModel() {
    private val _uiState = MutableStateFlow(DeliveryKuUiState())
    val uiState: StateFlow<DeliveryKuUiState> = _uiState.asStateFlow()

    fun setRole(role: UserRole) {
        _uiState.update { it.copy(activeRole = role) }
    }

    fun selectDistrict(district: District) {
        _uiState.update { it.copy(selectedDistrict = district, snackbarMessage = "Lokasi diubah ke ${district.name}") }
    }

    fun updateSearch(query: String) {
        _uiState.update { it.copy(searchQuery = query) }
    }

    fun selectCategory(category: String) {
        _uiState.update { it.copy(selectedCategory = category) }
    }

    fun addToCart(product: Product) {
        _uiState.update { state ->
            val existing = state.cart.find { it.product.id == product.id }
            val updatedCart = if (existing != null) {
                state.cart.map {
                    if (it.product.id == product.id) it.copy(quantity = it.quantity + 1) else it
                }
            } else {
                state.cart + CartItem(product = product, quantity = 1)
            }
            state.copy(cart = updatedCart, snackbarMessage = "${product.name} masuk ke keranjang")
        }
    }

    fun updateCartQty(productId: Int, delta: Int) {
        _uiState.update { state ->
            val updated = state.cart.mapNotNull { item ->
                if (item.product.id == productId) {
                    val nextQty = item.quantity + delta
                    if (nextQty <= 0) null else item.copy(quantity = nextQty)
                } else {
                    item
                }
            }
            state.copy(cart = updated)
        }
    }

    fun setVoucherCode(code: String) {
        _uiState.update { it.copy(voucherCode = code.uppercase().trim()) }
    }

    fun setPaymentMethod(method: String) {
        _uiState.update { it.copy(selectedPayment = method) }
    }

    fun setDeliveryAddress(address: String) {
        _uiState.update { it.copy(deliveryAddress = address) }
    }

    fun placeOrder() {
        _uiState.update { state ->
            if (state.cart.isEmpty()) {
                return@update state.copy(snackbarMessage = "Keranjang masih kosong")
            }
            val subtotal = state.cart.sumOf { it.lineSubtotal }
            val voucherDiscount = if (state.voucherCode == "INDRAMAYUHEMAT" && subtotal >= 25000) 5000 else 0
            val deliveryFee = state.selectedDistrict.baseDeliveryFee
            val serviceFee = state.serviceFee
            val commission = (subtotal * state.commissionRatePercent) / 100
            val merchantNet = subtotal - commission
            val driverNet = (deliveryFee * 85) / 100
            val total = (subtotal - voucherDiscount + deliveryFee + serviceFee).coerceAtLeast(0)
            val merchantName = state.cart.first().product.merchantName
            val summary = state.cart.joinToString(", ") { "${it.quantity}x ${it.product.name}" }
            val orderCode = "DK-${100 + state.orders.size + 1}"
            val pin = "${(1000..9999).random()}"

            val newOrder = Order(
                code = orderCode,
                merchantName = merchantName,
                customerName = "Nadia Putri",
                districtName = state.selectedDistrict.name,
                address = state.deliveryAddress,
                itemsSummary = summary,
                subtotal = subtotal,
                deliveryFee = deliveryFee,
                serviceFee = serviceFee,
                voucherDiscount = voucherDiscount,
                platformCommission = commission,
                merchantNetIncome = merchantNet,
                driverNetIncome = driverNet,
                totalAmount = total,
                paymentMethod = state.selectedPayment,
                status = OrderStatus.MENUNGGU_KONFIRMASI,
                verificationPin = pin,
            )

            state.copy(
                cart = emptyList(),
                orders = listOf(newOrder) + state.orders,
                snackbarMessage = "Pesanan $orderCode berhasil dibuat!",
            )
        }
    }

    fun advanceOrderStatus(orderCode: String, nextStatus: OrderStatus) {
        _uiState.update { state ->
            val updated = state.orders.map {
                if (it.code == orderCode) it.copy(status = nextStatus) else it
            }
            state.copy(orders = updated, snackbarMessage = "$orderCode -> ${nextStatus.label}")
        }
    }

    fun toggleMerchantStore() {
        _uiState.update { it.copy(merchantStoreOpen = !it.merchantStoreOpen) }
    }

    fun toggleDriverOnline() {
        _uiState.update { it.copy(driverOnline = !it.driverOnline) }
    }

    fun clearSnackbar() {
        _uiState.update { it.copy(snackbarMessage = null) }
    }
}

fun formatRupiah(amount: Int): String {
    val formatter = NumberFormat.getNumberInstance(Locale("in", "ID"))
    return "Rp ${formatter.format(amount)}"
}

private fun defaultDistricts(): List<District> = listOf(
    District(1, "Kec. Jatibarang", "45273", 6000),
    District(2, "Kec. Sukagumiwang", "45274", 7000),
    District(3, "Kec. Kertasemaya", "45274", 7000),
    District(4, "Kec. Widasari", "45271", 6500),
    District(5, "Kec. Tukdana", "45276", 7500),
    District(6, "Kec. Bangodua", "45272", 7500),
    District(7, "Kec. Krangkeng", "45284", 8000),
)

private fun defaultMerchants(): List<Merchant> = listOf(
    Merchant(1, "Warung Nasi Lengko Ibu Sari", "Kec. Jatibarang", "Kuliner Khas", 4.9),
    Merchant(2, "Ikan Bakar & Kerupuk Mang Dedi", "Kec. Kertasemaya", "Seafood & UMKM", 4.8),
    Merchant(3, "Kedai Es Mangga Gedong Gincu", "Kec. Widasari", "Minuman Segar", 4.9),
)

private fun defaultProducts(): List<Product> = listOf(
    Product(1, 1, "Warung Nasi Lengko Ibu Sari", "Nasi Lengko Komplit Ibu Sari", "Nasi & Lauk", 18000, 15000, 80, "Tahu, tempe, tauge, kucai & bumbu kacang khas Indramayu."),
    Product(2, 1, "Warung Nasi Lengko Ibu Sari", "Pindang Gombyang Kepala Ikan", "Nasi & Lauk", 38000, null, 30, "Kuah kuning asam segar rempah Indramayu."),
    Product(3, 3, "Kedai Es Mangga Gedong Gincu", "Es Mangga Gedong Gincu", "Minuman", 14000, 12000, 100, "Jus & potongan mangga gedong gincu asli."),
    Product(4, 2, "Ikan Bakar & Kerupuk Mang Dedi", "Ikan Etong Bakar Sambal", "Seafood", 42000, 39000, 25, "Ikan etong bakar arang + sambal terasi."),
    Product(5, 2, "Ikan Bakar & Kerupuk Mang Dedi", "Kerupuk Udang Asli 500g", "UMKM Lokal", 35000, null, 50, "Oleh-oleh UMKM kerupuk udang renyah."),
    Product(6, 2, "Ikan Bakar & Kerupuk Mang Dedi", "Paket Sembako Hemat", "Kebutuhan Harian", 68000, 65000, 40, "Beras 2.5kg, minyak 1L, gula 1kg."),
)

private fun defaultOrders(): List<Order> = listOf(
    Order(
        code = "DK-101",
        merchantName = "Warung Nasi Lengko Ibu Sari",
        customerName = "Nadia Putri",
        districtName = "Kec. Jatibarang",
        address = "Jl. Mayor Dasuki No. 48, Jatibarang Baru",
        itemsSummary = "1x Nasi Lengko Komplit, 1x Es Mangga Gedong Gincu",
        subtotal = 27000,
        deliveryFee = 6000,
        serviceFee = 2000,
        voucherDiscount = 5000,
        platformCommission = 4050,
        merchantNetIncome = 22950,
        driverNetIncome = 5100,
        totalAmount = 30000,
        paymentMethod = "COD (Bayar di Tempat)",
        status = OrderStatus.SELESAI,
        verificationPin = "4821",
    )
)
