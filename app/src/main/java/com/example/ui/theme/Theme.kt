package com.example.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

private val LightColors = lightColorScheme(
    primary = DkBlue,
    onPrimary = Color.White,
    primaryContainer = DkSurfaceVariant,
    onPrimaryContainer = DkNavy,
    secondary = DkGreen,
    onSecondary = Color.White,
    tertiary = DkNavy,
    background = DkBackground,
    onBackground = DkOnSurface,
    surface = DkSurface,
    onSurface = DkOnSurface,
    surfaceVariant = DkSurfaceVariant,
    onSurfaceVariant = DkOnSurfaceMuted,
    error = DkError,
)

private val DarkColors = darkColorScheme(
    primary = Color(0xFF60A5FA),
    onPrimary = Color(0xFF0A192F),
    primaryContainer = DkNavy,
    onPrimaryContainer = Color.White,
    secondary = DkGreen,
    onSecondary = Color.White,
    background = Color(0xFF0B1220),
    onBackground = Color(0xFFF1F5F9),
    surface = Color(0xFF131D31),
    onSurface = Color(0xFFF1F5F9),
    surfaceVariant = Color(0xFF1E293B),
    onSurfaceVariant = Color(0xFF94A3B8),
)

@Composable
fun DeliveryKuTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit,
) {
    val colorScheme = if (darkTheme) DarkColors else LightColors
    MaterialTheme(
        colorScheme = colorScheme,
        typography = DeliveryKuTypography,
        content = content,
    )
}
