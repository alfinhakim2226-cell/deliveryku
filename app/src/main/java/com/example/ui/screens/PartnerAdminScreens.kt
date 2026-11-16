package com.example.ui.screens

import androidx.compose.foundation.layout.Arrangement
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
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.CloudUpload
import androidx.compose.material.icons.filled.DeliveryDining
import androidx.compose.material.icons.filled.Store
import androidx.compose.material3.AssistChip
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Switch
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.example.model.OrderStatus
import com.example.ui.theme.DkBlue
import com.example.ui.theme.DkGreen
import com.example.viewmodel.DeliveryKuUiState
import com.example.viewmodel.DeliveryKuViewModel
import com.example.viewmodel.formatRupiah

@Composable
fun MerchantPortalScreen(
    state: DeliveryKuUiState,
    viewModel: DeliveryKuViewModel,
    modifier: Modifier = Modifier,
) {
    val completedNet = state.orders.filter { it.status == OrderStatus.SELESAI }.sumOf { it.merchantNetIncome }
    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        item {
            Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primaryContainer)) {
                Row(
                    modifier = Modifier.padding(16.dp).fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Column {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(Icons.Default.Store, contentDescription = null, tint = DkBlue)
                            Spacer(Modifier.width(6.dp))
                            Text("Panel Merchant UMKM", style = MaterialTheme.typography.titleMedium)
                        }
                        Text("Pendapatan Bersih Selesai: ${formatRupiah(completedNet)}", color = DkGreen, fontWeight = FontWeight.Bold)
                    }
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Text(if (state.merchantStoreOpen) "Buka" else "Tutup", fontWeight = FontWeight.SemiBold)
                        Spacer(Modifier.width(6.dp))
                        Switch(
                            checked = state.merchantStoreOpen,
                            onCheckedChange = { viewModel.toggleMerchantStore() },
                            modifier = Modifier.testTag("merchant_open_switch"),
                        )
                    }
                }
            }
        }

        item {
            Text("Kelola Pesanan Masuk", style = MaterialTheme.typography.titleMedium)
        }

        items(state.orders, key = { it.code }) { order ->
            Card(modifier = Modifier.fillMaxWidth()) {
                Column(modifier = Modifier.padding(14.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text("${order.code} • ${order.customerName}", fontWeight = FontWeight.Bold)
                        AssistChip(onClick = {}, label = { Text(order.status.label) })
                    }
                    Text(order.itemsSummary)
                    Text("Subtotal: ${formatRupiah(order.subtotal)} • Bersih Merchant: ${formatRupiah(order.merchantNetIncome)}", color = DkGreen, style = MaterialTheme.typography.bodyMedium)

                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        when (order.status) {
                            OrderStatus.MENUNGGU_KONFIRMASI -> {
                                Button(
                                    onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.PESANAN_DIKONFIRMASI) },
                                    colors = ButtonDefaults.buttonColors(containerColor = DkGreen),
                                    modifier = Modifier.heightIn(min = 48.dp).testTag("merchant_confirm_${order.code}"),
                                ) { Text("Konfirmasi") }
                                OutlinedButton(
                                    onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.DIBATALKAN) },
                                    modifier = Modifier.heightIn(min = 48.dp),
                                ) { Text("Tolak") }
                            }
                            OrderStatus.PESANAN_DIKONFIRMASI -> {
                                Button(
                                    onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.SEDANG_DISIAPKAN) },
                                    modifier = Modifier.heightIn(min = 48.dp),
                                ) { Text("Siapkan Pesanan") }
                            }
                            OrderStatus.SEDANG_DISIAPKAN -> {
                                Button(
                                    onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.MENUNGGU_DRIVER) },
                                    modifier = Modifier.heightIn(min = 48.dp).testTag("merchant_ready_${order.code}"),
                                ) { Text("Siap Diambil Kurir") }
                            }
                            else -> Unit
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun DriverPortalScreen(
    state: DeliveryKuUiState,
    viewModel: DeliveryKuViewModel,
    modifier: Modifier = Modifier,
) {
    val driverEarnings = state.orders.filter { it.status == OrderStatus.SELESAI }.sumOf { it.driverNetIncome }
    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        item {
            Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primaryContainer)) {
                Row(
                    modifier = Modifier.padding(16.dp).fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Column {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(Icons.Default.DeliveryDining, contentDescription = null, tint = DkBlue)
                            Spacer(Modifier.width(6.dp))
                            Text("Panel Driver Indramayu", style = MaterialTheme.typography.titleMedium)
                        }
                        Text("Total Penghasilan: ${formatRupiah(driverEarnings)}", color = DkGreen, fontWeight = FontWeight.Bold)
                    }
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Text(if (state.driverOnline) "Online" else "Offline", fontWeight = FontWeight.SemiBold)
                        Spacer(Modifier.width(6.dp))
                        Switch(
                            checked = state.driverOnline,
                            onCheckedChange = { viewModel.toggleDriverOnline() },
                            modifier = Modifier.testTag("driver_online_switch"),
                        )
                    }
                }
            }
        }

        items(state.orders, key = { it.code }) { order ->
            Card(modifier = Modifier.fillMaxWidth()) {
                Column(modifier = Modifier.padding(14.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text(order.code, fontWeight = FontWeight.Bold, color = DkBlue)
                        Text("Upah ${formatRupiah(order.driverNetIncome)}", color = DkGreen, fontWeight = FontWeight.Bold)
                    }
                    Text("Ambil: ${order.merchantName}")
                    Text("Antar: ${order.address} (${order.districtName})")
                    Text("Status: ${order.status.label}", style = MaterialTheme.typography.labelMedium)

                    when (order.status) {
                        OrderStatus.MENUNGGU_DRIVER -> Button(
                            onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.DRIVER_MENUJU_MERCHANT) },
                            modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp).testTag("driver_accept_${order.code}"),
                        ) { Text("Terima Order Pengantaran") }

                        OrderStatus.DRIVER_MENUJU_MERCHANT -> Button(
                            onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.PESANAN_DIAMBIL) },
                            modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp),
                        ) { Text("Konfirmasi Pesanan Diambil") }

                        OrderStatus.PESANAN_DIAMBIL -> Button(
                            onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.DALAM_PENGIRIMAN) },
                            modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp),
                        ) { Text("Mulai Pengantaran ke Pelanggan") }

                        OrderStatus.DALAM_PENGIRIMAN -> Button(
                            onClick = { viewModel.advanceOrderStatus(order.code, OrderStatus.SELESAI) },
                            colors = ButtonDefaults.buttonColors(containerColor = DkGreen),
                            modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp).testTag("driver_complete_${order.code}"),
                        ) {
                            Icon(Icons.Default.Check, contentDescription = null)
                            Spacer(Modifier.width(6.dp))
                            Text("Selesaikan Pengantaran (PIN ${order.verificationPin})")
                        }
                        else -> Unit
                    }
                }
            }
        }
    }
}

@Composable
fun ManagementPortalScreen(
    state: DeliveryKuUiState,
    isSuperadmin: Boolean,
    modifier: Modifier = Modifier,
) {
    val completed = state.orders.filter { it.status == OrderStatus.SELESAI }
    val gmv = completed.sumOf { it.totalAmount }
    val commission = completed.sumOf { it.platformCommission + it.serviceFee }

    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        item {
            Text(
                if (isSuperadmin) "Pusat Kendali Superadmin" else "Dashboard Admin Operasional",
                style = MaterialTheme.typography.titleLarge,
            )
        }
        item {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                Card(modifier = Modifier.weight(1f)) {
                    Column(Modifier.padding(14.dp)) {
                        Text("Total GMV Selesai", style = MaterialTheme.typography.bodyMedium)
                        Text(formatRupiah(gmv), fontWeight = FontWeight.Bold, color = DkBlue)
                    }
                }
                Card(modifier = Modifier.weight(1f)) {
                    Column(Modifier.padding(14.dp)) {
                        Text("Komisi + Layanan", style = MaterialTheme.typography.bodyMedium)
                        Text(formatRupiah(commission), fontWeight = FontWeight.Bold, color = DkGreen)
                    }
                }
            }
        }

        item {
            Card(modifier = Modifier.fillMaxWidth()) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Default.CloudUpload, contentDescription = null, tint = DkBlue)
                        Spacer(Modifier.width(8.dp))
                        Text("Paket Web PHP 8.4 + MySQL (InfinityFree Ready)", fontWeight = FontWeight.Bold)
                    }
                    Text(
                        "Source code web marketplace lengkap tersedia di folder proyek /delivery-ku/ dengan 36 tabel MySQL (schema.sql & seed.sql), proteksi .htaccess, 5 dashboard role, ekspor Excel/PDF, dan backup SQL.",
                        style = MaterialTheme.typography.bodyMedium,
                    )
                    Spacer(Modifier.height(4.dp))
                    Text("Wilayah Awal Kabupaten Indramayu (${state.districts.size} Kecamatan):", fontWeight = FontWeight.SemiBold)
                    Text(state.districts.joinToString(" • ") { it.name }, style = MaterialTheme.typography.bodyMedium, color = DkBlue)
                }
            }
        }

        items(state.orders, key = { it.code }) { order ->
            Card(modifier = Modifier.fillMaxWidth()) {
                Row(
                    modifier = Modifier.padding(14.dp).fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Column(Modifier.weight(1f)) {
                        Text("${order.code} • ${order.merchantName}", fontWeight = FontWeight.SemiBold)
                        Text("${order.customerName} (${order.districtName})", style = MaterialTheme.typography.bodyMedium)
                    }
                    Column(horizontalAlignment = Alignment.End) {
                        Text(formatRupiah(order.totalAmount), fontWeight = FontWeight.Bold, color = DkBlue)
                        Text(order.status.label, style = MaterialTheme.typography.labelMedium, color = DkGreen)
                    }
                }
            }
        }
    }
}
