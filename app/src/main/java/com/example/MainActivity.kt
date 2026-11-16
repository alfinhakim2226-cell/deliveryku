package com.example

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.BackHandler
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.AdminPanelSettings
import androidx.compose.material.icons.filled.DeliveryDining
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.ReceiptLong
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material.icons.filled.Storefront
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.FilterChip
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import com.example.model.UserRole
import com.example.ui.screens.CustomerCartCheckoutScreen
import com.example.ui.screens.CustomerHomeScreen
import com.example.ui.screens.CustomerOrdersScreen
import com.example.ui.screens.DriverPortalScreen
import com.example.ui.screens.ManagementPortalScreen
import com.example.ui.screens.MerchantPortalScreen
import com.example.ui.theme.DeliveryKuTheme
import com.example.viewmodel.DeliveryKuViewModel

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            DeliveryKuTheme {
                DeliveryKuApp()
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DeliveryKuApp(viewModel: DeliveryKuViewModel = viewModel()) {
    val state by viewModel.uiState.collectAsStateWithLifecycle()
    val snackbarHostState = remember { SnackbarHostState() }
    var customerTab by rememberSaveable { mutableIntStateOf(0) }

    LaunchedEffect(state.snackbarMessage) {
        state.snackbarMessage?.let { msg ->
            snackbarHostState.showSnackbar(msg)
            viewModel.clearSnackbar()
        }
    }

    // BackHandler returns to Customer Home if on secondary tabs or non-Pelanggan role
    BackHandler(enabled = state.activeRole != UserRole.PELANGGAN || customerTab != 0) {
        if (state.activeRole != UserRole.PELANGGAN) {
            viewModel.setRole(UserRole.PELANGGAN)
        } else {
            customerTab = 0
        }
    }

    Scaffold(
        modifier = Modifier.fillMaxSize(),
        contentWindowInsets = WindowInsets(0, 0, 0, 0),
        snackbarHost = { SnackbarHost(snackbarHostState) },
        topBar = {
            Surface(shadowElevation = 2.dp) {
                Column {
                    TopAppBar(
                        title = {
                            Column {
                                Text("Delivery Ku", fontWeight = FontWeight.Bold)
                                Text(
                                    "Marketplace Indramayu • ${state.activeRole.label}",
                                    style = MaterialTheme.typography.bodyMedium,
                                )
                            }
                        },
                        colors = TopAppBarDefaults.topAppBarColors(
                            containerColor = MaterialTheme.colorScheme.primary,
                            titleContentColor = MaterialTheme.colorScheme.onPrimary,
                        ),
                    )
                    LazyRow(
                        contentPadding = PaddingValues(horizontal = 12.dp, vertical = 6.dp),
                        horizontalArrangement = Arrangement.spacedBy(8.dp),
                    ) {
                        items(UserRole.entries) { role ->
                            FilterChip(
                                selected = state.activeRole == role,
                                onClick = { viewModel.setRole(role) },
                                label = { Text(role.label) },
                                modifier = Modifier
                                    .heightIn(min = 48.dp)
                                    .testTag("role_chip_${role.name.lowercase()}"),
                            )
                        }
                    }
                }
            }
        },
        bottomBar = {
            if (state.activeRole == UserRole.PELANGGAN) {
                NavigationBar {
                    NavigationBarItem(
                        selected = customerTab == 0,
                        onClick = { customerTab = 0 },
                        icon = { Icon(Icons.Default.Home, contentDescription = "Beranda") },
                        label = { Text("Beranda") },
                        modifier = Modifier.testTag("nav_home"),
                    )
                    NavigationBarItem(
                        selected = customerTab == 1,
                        onClick = { customerTab = 1 },
                        icon = { Icon(Icons.Default.ShoppingCart, contentDescription = "Keranjang") },
                        label = { Text("Keranjang (${state.cart.sumOf { it.quantity }})") },
                        modifier = Modifier.testTag("nav_cart"),
                    )
                    NavigationBarItem(
                        selected = customerTab == 2,
                        onClick = { customerTab = 2 },
                        icon = { Icon(Icons.Default.ReceiptLong, contentDescription = "Pesanan") },
                        label = { Text("Pesanan") },
                        modifier = Modifier.testTag("nav_orders"),
                    )
                }
            } else {
                NavigationBar {
                    NavigationBarItem(
                        selected = true,
                        onClick = {},
                        icon = {
                            val icon = when (state.activeRole) {
                                UserRole.MERCHANT -> Icons.Default.Storefront
                                UserRole.DRIVER -> Icons.Default.DeliveryDining
                                else -> Icons.Default.AdminPanelSettings
                            }
                            Icon(icon, contentDescription = state.activeRole.label)
                        },
                        label = { Text("Panel ${state.activeRole.label}") },
                    )
                }
            }
        },
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            val contentModifier = Modifier.widthIn(max = 760.dp).fillMaxSize()
            when (state.activeRole) {
                UserRole.PELANGGAN -> when (customerTab) {
                    0 -> CustomerHomeScreen(
                        state = state,
                        viewModel = viewModel,
                        onGoCart = { customerTab = 1 },
                        modifier = contentModifier,
                    )
                    1 -> CustomerCartCheckoutScreen(
                        state = state,
                        viewModel = viewModel,
                        onOrderPlaced = { customerTab = 2 },
                        modifier = contentModifier,
                    )
                    else -> CustomerOrdersScreen(
                        state = state,
                        viewModel = viewModel,
                        modifier = contentModifier,
                    )
                }
                UserRole.MERCHANT -> MerchantPortalScreen(
                    state = state,
                    viewModel = viewModel,
                    modifier = contentModifier,
                )
                UserRole.DRIVER -> DriverPortalScreen(
                    state = state,
                    viewModel = viewModel,
                    modifier = contentModifier,
                )
                UserRole.ADMIN -> ManagementPortalScreen(
                    state = state,
                    isSuperadmin = false,
                    modifier = contentModifier,
                )
                UserRole.SUPERADMIN -> ManagementPortalScreen(
                    state = state,
                    isSuperadmin = true,
                    modifier = contentModifier,
                )
            }
        }
    }
}
