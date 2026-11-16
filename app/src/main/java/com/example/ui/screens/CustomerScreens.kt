package com.example.ui.screens

import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.AddShoppingCart
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material.icons.filled.Star
import androidx.compose.material.icons.filled.Storefront
import androidx.compose.material3.AssistChip
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.DropdownMenu
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.ElevatedCard
import androidx.compose.material3.FilterChip
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.RadioButton
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.minimumInteractiveComponentSize
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextDecoration
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import com.example.R
import com.example.model.OrderStatus
import com.example.ui.theme.DkBlue
import com.example.ui.theme.DkGreen
import com.example.ui.theme.DkNavy
import com.example.viewmodel.DeliveryKuUiState
import com.example.viewmodel.DeliveryKuViewModel
import com.example.viewmodel.formatRupiah

@Composable
fun CustomerHomeScreen(
    state: DeliveryKuUiState,
    viewModel: DeliveryKuViewModel,
    onGoCart: () -> Unit,
    modifier: Modifier = Modifier,
) {
    var districtExpanded by remember { mutableStateOf(false) }
    val categories = listOf("Semua", "Nasi & Lauk", "Minuman", "Seafood", "UMKM Lokal", "Kebutuhan Harian")

    val filteredProducts = remember(state.products, state.searchQuery, state.selectedCategory) {
        state.products.filter { prod ->
            val matchCat = state.selectedCategory == "Semua" || prod.category == state.selectedCategory
            val q = state.searchQuery.trim().lowercase()
            val matchQ = q.isEmpty() || prod.name.lowercase().contains(q) || prod.merchantName.lowercase().contains(q)
            matchCat && matchQ
        }
    }

    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp),
    ) {
        // Location & Cart row
        item {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Box {
                    OutlinedButton(
                        onClick = { districtExpanded = true },
                        modifier = Modifier
                            .heightIn(min = 48.dp)
                            .testTag("district_selector_button"),
                    ) {
                        Icon(Icons.Default.LocationOn, contentDescription = "Lokasi Kecamatan", tint = DkBlue)
                        Spacer(Modifier.width(6.dp))
                        Text("${state.selectedDistrict.name}, Indramayu", maxLines = 1, overflow = TextOverflow.Ellipsis)
                    }
                    DropdownMenu(expanded = districtExpanded, onDismissRequest = { districtExpanded = false }) {
                        state.districts.forEach { d ->
                            DropdownMenuItem(
                                text = { Text("${d.name} (${d.postalCode}) • Ongkir ${formatRupiah(d.baseDeliveryFee)}") },
                                onClick = {
                                    viewModel.selectDistrict(d)
                                    districtExpanded = false
                                },
                            )
                        }
                    }
                }

                Button(
                    onClick = onGoCart,
                    colors = ButtonDefaults.buttonColors(containerColor = DkGreen),
                    modifier = Modifier
                        .heightIn(min = 48.dp)
                        .testTag("open_cart_button"),
                ) {
                    Icon(Icons.Default.ShoppingCart, contentDescription = "Keranjang")
                    Spacer(Modifier.width(6.dp))
                    Text("${state.cart.sumOf { it.quantity }}")
                }
            }
        }

        // Hero Banner
        item {
            ElevatedCard(
                shape = RoundedCornerShape(20.dp),
                modifier = Modifier.fillMaxWidth(),
            ) {
                Box(modifier = Modifier.fillMaxWidth().height(180.dp)) {
                    Image(
                        painter = painterResource(id = R.drawable.img_hero_indramayu),
                        contentDescription = "Ilustrasi Delivery Ku Kabupaten Indramayu",
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize(),
                    )
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(
                                Brush.verticalGradient(
                                    colors = listOf(Color.Transparent, DkNavy.copy(alpha = 0.85f)),
                                )
                            )
                            .padding(16.dp),
                        contentAlignment = Alignment.BottomStart,
                    ) {
                        Column {
                            Surface(color = DkGreen, shape = RoundedCornerShape(8.dp)) {
                                Text(
                                    "INDRAMAYU HEMAT • VOUCHER INDRAMAYUHEMAT",
                                    color = Color.White,
                                    style = MaterialTheme.typography.labelMedium,
                                    modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp),
                                )
                            }
                            Spacer(Modifier.height(6.dp))
                            Text(
                                "Delivery Ku Indramayu",
                                color = Color.White,
                                style = MaterialTheme.typography.titleLarge,
                            )
                            Text(
                                "Kuliner Khas & Produk UMKM Lokal Cepat Sampai",
                                color = Color.White.copy(alpha = 0.9f),
                                style = MaterialTheme.typography.bodyMedium,
                            )
                        }
                    }
                }
            }
        }

        // Search input
        item {
            OutlinedTextField(
                value = state.searchQuery,
                onValueChange = viewModel::updateSearch,
                placeholder = { Text("Cari Nasi Lengko, Ikan Bakar, Es Mangga...") },
                leadingIcon = { Icon(Icons.Default.Search, contentDescription = "Cari menu") },
                singleLine = true,
                shape = RoundedCornerShape(14.dp),
                modifier = Modifier
                    .fillMaxWidth()
                    .testTag("search_menu_input"),
            )
        }

        // Categories
        item {
            LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                items(categories) { cat ->
                    FilterChip(
                        selected = state.selectedCategory == cat,
                        onClick = { viewModel.selectCategory(cat) },
                        label = { Text(cat) },
                        modifier = Modifier.heightIn(min = 48.dp),
                    )
                }
            }
        }

        // Recommended Merchants
        item {
            Text("Merchant Rekomendasi Indramayu", style = MaterialTheme.typography.titleMedium)
            Spacer(Modifier.height(8.dp))
            LazyRow(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                items(state.merchants) { m ->
                    Card(
                        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                        modifier = Modifier.width(240.dp),
                    ) {
                        Column(modifier = Modifier.padding(14.dp)) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Icon(Icons.Default.Storefront, contentDescription = null, tint = DkBlue)
                                Spacer(Modifier.width(8.dp))
                                Text(m.storeName, fontWeight = FontWeight.SemiBold, maxLines = 1, overflow = TextOverflow.Ellipsis)
                            }
                            Spacer(Modifier.height(4.dp))
                            Text("${m.category} • ${m.districtName}", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            Spacer(Modifier.height(6.dp))
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Icon(Icons.Default.Star, contentDescription = "Rating", tint = Color(0xFFF59E0B))
                                Spacer(Modifier.width(4.dp))
                                Text("${m.rating} • Buka", style = MaterialTheme.typography.labelMedium, color = DkGreen)
                            }
                        }
                    }
                }
            }
        }

        // Menu items
        item {
            Text("Menu Populer & Promo Hari Ini", style = MaterialTheme.typography.titleMedium)
        }

        items(filteredProducts, key = { it.id }) { prod ->
            Card(
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                modifier = Modifier.fillMaxWidth(),
            ) {
                Row(
                    modifier = Modifier.padding(14.dp).fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Column(modifier = Modifier.weight(1f).padding(end = 12.dp)) {
                        Text(prod.name, style = MaterialTheme.typography.titleMedium)
                        Text(prod.merchantName, style = MaterialTheme.typography.bodyMedium, color = DkBlue)
                        Text(prod.description, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant, maxLines = 2, overflow = TextOverflow.Ellipsis)
                        Spacer(Modifier.height(6.dp))
                        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            Text(
                                formatRupiah(prod.promoPrice ?: prod.price),
                                fontWeight = FontWeight.Bold,
                                color = DkBlue,
                            )
                            if (prod.promoPrice != null) {
                                Text(
                                    formatRupiah(prod.price),
                                    style = MaterialTheme.typography.bodyMedium.copy(textDecoration = TextDecoration.LineThrough),
                                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                                )
                            }
                        }
                    }
                    Button(
                        onClick = { viewModel.addToCart(prod) },
                        modifier = Modifier
                            .heightIn(min = 48.dp)
                            .testTag("add_to_cart_${prod.id}"),
                    ) {
                        Icon(Icons.Default.AddShoppingCart, contentDescription = "Tambah ${prod.name}")
                    }
                }
            }
        }
    }
}

@Composable
fun CustomerCartCheckoutScreen(
    state: DeliveryKuUiState,
    viewModel: DeliveryKuViewModel,
    onOrderPlaced: () -> Unit,
    modifier: Modifier = Modifier,
) {
    val subtotal = state.cart.sumOf { it.lineSubtotal }
    val voucherDiscount = if (state.voucherCode == "INDRAMAYUHEMAT" && subtotal >= 25000) 5000 else 0
    val deliveryFee = if (state.cart.isEmpty()) 0 else state.selectedDistrict.baseDeliveryFee
    val serviceFee = if (state.cart.isEmpty()) 0 else state.serviceFee
    val total = (subtotal - voucherDiscount + deliveryFee + serviceFee).coerceAtLeast(0)
    val paymentMethods = listOf("COD (Bayar di Tempat)", "Transfer Bank", "Transfer Manual (Upload Bukti)")

    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(14.dp),
    ) {
        item {
            Text("Keranjang & Checkout (${state.selectedDistrict.name})", style = MaterialTheme.typography.titleLarge)
        }

        if (state.cart.isEmpty()) {
            item {
                Card(modifier = Modifier.fillMaxWidth()) {
                    Column(modifier = Modifier.padding(24.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                        Icon(Icons.Default.ShoppingCart, contentDescription = null, tint = DkBlue)
                        Spacer(Modifier.height(8.dp))
                        Text("Keranjang belanja masih kosong. Pilih menu dari Beranda.")
                    }
                }
            }
        } else {
            items(state.cart, key = { it.product.id }) { item ->
                Card(modifier = Modifier.fillMaxWidth()) {
                    Row(
                        modifier = Modifier.padding(12.dp).fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically,
                    ) {
                        Column(modifier = Modifier.weight(1f)) {
                            Text(item.product.name, fontWeight = FontWeight.SemiBold)
                            Text("${formatRupiah(item.effectiveUnitPrice)} x ${item.quantity}", color = DkBlue)
                        }
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            OutlinedButton(
                                onClick = { viewModel.updateCartQty(item.product.id, -1) },
                                modifier = Modifier.minimumInteractiveComponentSize(),
                            ) { Text("-") }
                            Text(" ${item.quantity} ", fontWeight = FontWeight.Bold)
                            OutlinedButton(
                                onClick = { viewModel.updateCartQty(item.product.id, +1) },
                                modifier = Modifier.minimumInteractiveComponentSize(),
                            ) { Text("+") }
                        }
                    }
                }
            }

            item {
                OutlinedTextField(
                    value = state.deliveryAddress,
                    onValueChange = viewModel::setDeliveryAddress,
                    label = { Text("Alamat Lengkap Pengiriman") },
                    modifier = Modifier.fillMaxWidth().testTag("checkout_address_input"),
                )
            }

            item {
                OutlinedTextField(
                    value = state.voucherCode,
                    onValueChange = viewModel::setVoucherCode,
                    label = { Text("Kode Voucher (Coba: INDRAMAYUHEMAT)") },
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth().testTag("checkout_voucher_input"),
                )
            }

            item {
                Card(modifier = Modifier.fillMaxWidth()) {
                    Column(modifier = Modifier.padding(14.dp)) {
                        Text("Metode Pembayaran", fontWeight = FontWeight.SemiBold)
                        paymentMethods.forEach { pm ->
                            Row(
                                verticalAlignment = Alignment.CenterVertically,
                                modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp),
                            ) {
                                RadioButton(
                                    selected = state.selectedPayment == pm,
                                    onClick = { viewModel.setPaymentMethod(pm) },
                                )
                                Text(pm)
                            }
                        }
                    }
                }
            }

            item {
                Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primaryContainer), modifier = Modifier.fillMaxWidth()) {
                    Column(modifier = Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                            Text("Subtotal Produk")
                            Text(formatRupiah(subtotal))
                        }
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                            Text("Ongkos Kirim (${state.selectedDistrict.name})")
                            Text(formatRupiah(deliveryFee))
                        }
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                            Text("Biaya Layanan")
                            Text(formatRupiah(serviceFee))
                        }
                        if (voucherDiscount > 0) {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text("Diskon Voucher", color = DkGreen)
                                Text("-${formatRupiah(voucherDiscount)}", color = DkGreen)
                            }
                        }
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                            Text("Total Pembayaran", fontWeight = FontWeight.Bold)
                            Text(formatRupiah(total), fontWeight = FontWeight.Bold, color = DkBlue)
                        }
                        Spacer(Modifier.height(8.dp))
                        Button(
                            onClick = {
                                viewModel.placeOrder()
                                onOrderPlaced()
                            },
                            colors = ButtonDefaults.buttonColors(containerColor = DkGreen),
                            modifier = Modifier.fillMaxWidth().heightIn(min = 50.dp).testTag("place_order_button"),
                        ) {
                            Text("Buat Pesanan Sekarang", fontWeight = FontWeight.Bold)
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun CustomerOrdersScreen(
    state: DeliveryKuUiState,
    viewModel: DeliveryKuViewModel,
    modifier: Modifier = Modifier,
) {
    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        item {
            Text("Pesanan & Pelacakan Langsung", style = MaterialTheme.typography.titleLarge)
        }
        items(state.orders, key = { it.code }) { order ->
            Card(modifier = Modifier.fillMaxWidth()) {
                Column(modifier = Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text(order.code, fontWeight = FontWeight.Bold, color = DkBlue)
                        AssistChip(onClick = {}, label = { Text(order.status.label) })
                    }
                    Text(order.merchantName, fontWeight = FontWeight.SemiBold)
                    Text(order.itemsSummary, style = MaterialTheme.typography.bodyMedium)
                    Text("Tujuan: ${order.address} (${order.districtName})", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    Text("PIN Penerimaan Kurir: ${order.verificationPin} • ${order.paymentMethod}", style = MaterialTheme.typography.labelMedium, color = DkNavy)
                    Text("Total: ${formatRupiah(order.totalAmount)}", fontWeight = FontWeight.Bold)

                    if (order.status == OrderStatus.DALAM_PENGIRIMAN) {
                        Button(
                            onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.SELESAI) },
                            colors = ButtonDefaults.buttonColors(containerColor = DkGreen),
                            modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp).testTag("confirm_received_${order.code}"),
                        ) {
                            Icon(Icons.Default.CheckCircle, contentDescription = null)
                            Spacer(Modifier.width(6.dp))
                            Text("Konfirmasi Pesanan Diterima")
                        }
                    }
                }
            }
        }
    }
}
