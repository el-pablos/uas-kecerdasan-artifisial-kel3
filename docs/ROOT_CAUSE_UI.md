# Root Cause: "Kenapa UI ga berubah?"

## Fakta
1. `GET /` → redirect ke `route('sentinel.dashboard')` → `/dashboard`
2. `/dashboard` → `LogAnalysisController@dashboard` → render `sentinel.dashboard` → extends `layouts.master`
3. `layouts.master` → include `layouts.sidebar-sentinel` (sidebar LAMA: Log Sentinel)
4. Semua halaman CTI (threats, knowledge, cases, dll) → extends `layouts.master-cti` → include `layouts.sidebar-cti` (sidebar BARU: OpenCTI style)
5. `RouteServiceProvider::HOME = '/'` → setelah login redirect ke `/` → `/dashboard` → sentinel

## Kesimpulan
User login → masuk `/dashboard` → lihat layout `master` + `sidebar-sentinel` (UI lama).
Sidebar CTI (Threats, Knowledge, Cases, dll) HANYA muncul kalau user manual buka `/threats/actors` atau `/knowledge/entities`.

Tidak ada "CTI Dashboard" — menu "Dashboard" di sidebar-cti malah ngarah ke `route('sentinel.dashboard')` yang pake layout sentinel.

## Solusi
1. Bikin CTI Dashboard beneran (`/cti`) dengan layout `master-cti`
2. Ubah landing `/` → redirect ke `/cti`
3. Ubah `HOME` → `/cti`
4. Pindah sentinel dashboard ke `/sentinel/dashboard`
5. Tambah mode switcher di topbar
