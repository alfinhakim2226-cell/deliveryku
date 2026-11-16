/**
 * Delivery Ku - Vanilla JS & AJAX Polling (Kompatibel InfinityFree)
 */
document.addEventListener('DOMContentLoaded', () => {
  // Auto-hide flash alert after 6s
  document.querySelectorAll('.alert-autoclose').forEach((el) => {
    setTimeout(() => {
      el.classList.add('fade');
      setTimeout(() => el.remove(), 400);
    }, 6000);
  });

  // Konfirmasi modal / dialog untuk tindakan penting
  document.querySelectorAll('[data-confirm]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      const msg = btn.getAttribute('data-confirm') || 'Apakah Anda yakin melanjutkan tindakan ini?';
      if (!window.confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // Polling status pesanan langsung pada halaman detail pesanan pelanggan
  const tracker = document.getElementById('live-order-tracker');
  if (tracker) {
    const apiUrl = tracker.getAttribute('data-api-url');
    const badgeEl = document.getElementById('live-order-status-label');
    const driverEl = document.getElementById('live-driver-info');
    if (apiUrl) {
      setInterval(async () => {
        try {
          const resp = await fetch(apiUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          if (!resp.ok) return;
          const json = await resp.json();
          if (json.ok && json.data) {
            if (badgeEl && badgeEl.textContent !== json.data.status_label) {
              badgeEl.textContent = json.data.status_label;
            }
            if (driverEl && json.data.driver_name) {
              driverEl.textContent = `${json.data.driver_name} (${json.data.license_plate || '-'}) • ${json.data.driver_phone || ''}`;
            }
          }
        } catch (err) {
          // Silent retry on next poll interval
        }
      }, 15000);
    }
  }

  // Polling badge notifikasi belum dibaca (wajar setiap 25 detik)
  const notifBadge = document.getElementById('notif-unread-badge');
  if (notifBadge) {
    const notifUrl = notifBadge.getAttribute('data-api-url');
    if (notifUrl) {
      const refreshNotif = async () => {
        try {
          const r = await fetch(notifUrl);
          if (!r.ok) return;
          const res = await r.json();
          if (res.ok) {
            notifBadge.textContent = res.unread > 0 ? String(res.unread) : '';
            notifBadge.style.display = res.unread > 0 ? 'inline-block' : 'none';
          }
        } catch (e) {}
      };
      refreshNotif();
      setInterval(refreshNotif, 25000);
    }
  }
});
