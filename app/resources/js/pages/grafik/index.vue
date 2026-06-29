<script setup>
import { router,Link } from '@inertiajs/vue3'
import Navbar from '@/components/Navbar.vue';
defineOptions({ layout: null })

// ── Props dari GrafikController::index() ─────────────────────────────────
const props = defineProps({
  summaryCards:      { type: Object, default: () => ({}) },
  revenueTrend:      { type: Object, default: () => ({ buckets: [], yAxisLabels: [] }) },
  revenueByCategory: { type: Array,  default: () => [] },
  topStok:           { type: Array,  default: () => [] },
  topTerjual:        { type: Array,  default: () => [] },
  pesananTrend:      { type: Object, default: () => ({ buckets: [], yAxisLabels: [] }) },
  topPesanan:        { type: Array,  default: () => [] },
  servisTrend:       { type: Object, default: () => ({ buckets: [], yAxisLabels: [] }) },
  topServis:         { type: Array,  default: () => [] },
  activePeriod:      { type: String, default: 'YTD' },
})

const periods = ['1D', '1W', '1M', '3M', 'YTD', '1Y', '5Y']

// Warna tetap untuk bar revenue by category
const categoryColorMap = {
  'Pesanan': '#2563eb',   // biru
  'Servis':  '#7c3aed',   // ungu
  'Terjual': '#f97316',   // orange
}
const defaultCategoryColor = '#64748b' // fallback abu

function getCategoryColor(kategori) {
  return categoryColorMap[kategori] || defaultCategoryColor
}

// Ganti period → Inertia request baru ke /grafik?period=X
function changePeriod(period) {
  router.get('/grafik', { period }, {
    preserveState:  true,
    preserveScroll: true,
    replace:        true,
  })
}

// Menampilkan nominal rupiah utuh tanpa penyingkatan
function formatRupiah(value) {
  if (!value && value !== 0) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value);
}

// Menampilkan angka kuantitas utuh (misal: 1.500 alih-alih 1.5K)
function formatCount(value) {
  if (!value && value !== 0) return '0';
  return value.toLocaleString('id-ID');
}

// Format persentase dengan 2 angka di belakang koma (contoh: 3,90%)
function formatPercent(value) {
  if (value === null || value === undefined || isNaN(value)) return '0,00';
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
}

// ── Trend helpers (3 state: naik/turun/stagnan) ──────────────────────────
function trendColor(val) {
  const v = val ?? 0;
  if (v > 0) return '#16a34a';   // hijau
  if (v < 0) return '#dc2626';   // merah
  return '#9ca3af';              // abu-abu (stagnan)
}

function trendBg(val) {
  const v = val ?? 0;
  if (v > 0) return '#dcfce7';
  if (v < 0) return '#fee2e2';
  return '#f3f4f6';
}

function trendIcon(val) {
  const v = val ?? 0;
  if (v > 0) return 'trending_up';
  if (v < 0) return 'trending_down';
  return 'trending_flat';
}

// Background & border untuk card berdasarkan trend
function cardBg(val) {
  const v = val ?? 0;
  if (v > 0) return '#f0fdf4';   // hijau muda
  if (v < 0) return '#fef2f2';   // merah muda
  return '#FFFFFF';              // default putih solid
}

function cardBorder(val) {
  const v = val ?? 0;
  if (v > 0) return '1px solid rgba(22,163,74,0.25)';
  if (v < 0) return '1px solid rgba(220,38,38,0.25)';
  return '1px solid #E2E8F0';   // default border solid
}
</script>

<template>
<div class="min-h-screen" style="background-color: #F4F5F6;">
  <Navbar />
  <main class="flex-1 min-h-screen w-full">
    <div class="pt-8 px-6 pb-12 max-w-7xl mx-auto space-y-8">

      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
          <h2 class="text-3xl font-light tracking-tight font-display mb-1" style="color: #131722;">Performa Pendapatan</h2>
          <p class="text-sm" style="color: #64748B;">Insight data keuangan operasional gudang.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="rounded-lg p-6 shadow-sm flex flex-col justify-between h-36 transition-colors duration-500"
          :style="{
            backgroundColor: cardBg(summaryCards.revenueChangePercent) ,
            border: cardBorder(summaryCards.revenueChangePercent) 
          }"
        >
          <span class="text-xs font-semibold uppercase tracking-widest font-label" style="color: #64748B;">Total Pendapatan</span>
          <div>
            <div class="text-2xl md:text-3xl font-light tracking-tight font-display truncate" style="color: #131722;" :title="formatRupiah(summaryCards.totalRevenue)">
              {{ formatRupiah(summaryCards.totalRevenue) }}
            </div>
            <div class="flex items-center mt-2">
              <span
                class="flex items-center px-2 py-0.5 rounded-full text-xs font-medium mr-2"
                :style="{
                  color: trendColor(summaryCards.revenueChangePercent),
                  backgroundColor: trendBg(summaryCards.revenueChangePercent)
                }"
              >
                <span class="material-symbols-outlined text-[14px] mr-1">
                  {{ trendIcon(summaryCards.revenueChangePercent) }}
                </span>
                {{ (summaryCards.revenueChangePercent ?? 0) > 0 ? '+' : '' }}{{ formatPercent(summaryCards.revenueChangePercent) }}%
              </span>
              <span class="text-xs" style="color: #64748B;">vs bulan lalu</span>
            </div>
          </div>
        </div>

        <div class="rounded-lg p-6 shadow-sm flex flex-col justify-between h-36 transition-colors duration-500"
          :style="{
            backgroundColor: cardBg(summaryCards.oneYearGrowth) ,
            border: cardBorder(summaryCards.oneYearGrowth) 
          }"
        >
          <span class="text-xs font-semibold uppercase tracking-widest font-label" style="color: #64748B;">Pertumbuhan YoY </span>
          <div>
            <div class="text-3xl font-light tracking-tight font-display" :style="{ color: trendColor(summaryCards.oneYearGrowth) }">
              {{ (summaryCards.oneYearGrowth ?? 0) > 0 ? '+' : '' }}{{ formatPercent(summaryCards.oneYearGrowth) }}%
            </div>
            <div class="flex items-center mt-2">
              <span
                class="flex items-center px-2 py-0.5 rounded-full text-xs font-medium mr-2"
                :style="{
                  color: trendColor(summaryCards.oneYearGrowth),
                  backgroundColor: trendBg(summaryCards.oneYearGrowth)
                }"
              >
                <span class="material-symbols-outlined text-[14px] mr-1">
                  {{ trendIcon(summaryCards.oneYearGrowth) }}
                </span>
                {{ (summaryCards.oneYearGrowth ?? 0) > 0 ? '+' : '' }}{{ formatPercent(summaryCards.oneYearGrowth) }}%
              </span>
              <span class="text-xs" style="color: #64748B;">vs bulan yang sama tahun lalu</span>
            </div>
          </div>
        </div>

        <div class="rounded-lg p-6 shadow-sm flex flex-col justify-between h-36 relative overflow-hidden" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
          <div class="absolute right-0 bottom-0 opacity-15 pointer-events-none">
            <span class="material-symbols-outlined text-[100px] text-amber-500 drop-shadow-[0_6px_20px_rgba(245,158,11,0.35)]">
              military_tech
            </span>
          </div>
          <span class="text-xs font-semibold uppercase tracking-widest font-label relative z-10" style="color: #64748B;">Periode Pendapatan Tertinggi</span>
          <div class="relative z-10">
            <div class="text-3xl font-light tracking-tight font-display" style="color: #131722;">
              {{ summaryCards.peakPeriod || '—' }}
            </div>
            <div class="flex items-center mt-2 truncate">
              <span class="text-xs truncate" style="color: #64748B;" :title="formatRupiah(summaryCards.peakRevenue)">Pendapatan tertinggi: {{ formatRupiah(summaryCards.peakRevenue) }} </span>
            </div>
          </div>
        </div>

      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 rounded-lg shadow-sm flex flex-col h-[500px]" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
          <div class="p-6 pb-2 flex justify-between items-center" style="border-bottom: 1px solid #E2E8F0;">
            <div class="flex items-center gap-3">
              <h3 class="font-bold font-headline" style="color: #131722;">Analisis Tren Pendapatan</h3>
              <span
                v-if="revenueTrend.trendPercent != null"
                class="flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                :style="{
                  color: trendColor(revenueTrend.trendPercent),
                  backgroundColor: trendBg(revenueTrend.trendPercent)
                }"
              >
                <span class="material-symbols-outlined text-[14px] mr-1">
                  {{ trendIcon(revenueTrend.trendPercent) }}
                </span>
                {{ (revenueTrend.trendPercent ?? 0) > 0 ? '+' : '' }}{{ formatPercent(revenueTrend.trendPercent) }}%
              </span>
            </div>
            <div class="flex rounded-DEFAULT p-1 overflow-x-auto no-scrollbar gap-1" style="background-color: #ECEFF1;">
              <button
                v-for="p in periods"
                :key="p"
                @click="changePeriod(p)"
                class="px-3 py-1 text-xs font-medium rounded-DEFAULT transition-all duration-300 ease-out transform hover:scale-105 active:scale-95 whitespace-nowrap"
                :class="activePeriod === p
                  ? 'font-bold shadow-sm bg-[#FFFFFF]'
                  : 'hover:text-green-600 hover:bg-[#F8F9FA]'"
                :style="activePeriod === p ? { color: '#16a34a', boxShadow: '0 0 0 1px #86efac' } : { color: '#64748B' }"
              >{{ p }}</button>
            </div>
          </div>
          <div class="p-6 flex-1 relative flex items-end justify-between pl-28 pr-6 pb-12 pt-8">
            <div class="absolute inset-0 p-6 pb-12 pl-28 pr-6 pointer-events-none flex flex-col justify-between">
              <div v-for="i in 4" :key="i" class="w-full" style="border-top: 1px solid #E2E8F0;"></div>
              <div class="w-full" style="border-top: 1px solid #E2E8F0;"></div>
            </div>

            <template v-if="revenueTrend.buckets && revenueTrend.buckets.length">
              <div
                v-for="bucket in revenueTrend.buckets"
                :key="bucket.label"
                class="relative z-10 flex flex-col items-center group h-full justify-end"
                style="flex: 1;"
              >
                <div
                  class="w-full max-w-[32px] rounded-t-sm shadow-sm relative cursor-pointer transition-colors"
                  :class="bucket.height_percent === 100 ? 'bg-green-600' : 'bg-green-300 group-hover:bg-green-500'"
                  :style="{ height: Math.max(bucket.height_percent, 2) + '%', opacity: 1 }"
                >
                  <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 shadow-lg rounded-DEFAULT p-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none w-max min-w-[140px] z-50" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                    <p class="text-[10px] font-label uppercase mb-1 text-center" style="color: #64748B;">{{ bucket.label }}</p>
                    <p class="text-sm font-bold text-center" style="color: #131722;">{{ formatRupiah(bucket.revenue) }}</p>
                  </div>
                </div>
                <div class="absolute -bottom-8 text-[10px] font-label text-center w-full truncate" style="color: #64748B;">{{ bucket.label }}</div>
              </div>
            </template>
            <div v-else class="w-full h-full flex items-center justify-center text-sm" style="color: #64748B;">
              Belum ada data revenue.
            </div>

            <div class="absolute left-2 top-8 bottom-12 w-24 pr-2 flex flex-col justify-between text-[10px] font-label pointer-events-none text-right" style="color: #64748B;">
              <span v-for="label in revenueTrend.yAxisLabels" :key="label" class="truncate" :title="label">{{ label }}</span>
            </div>
          </div>
        </div>

        <div class="rounded-lg p-6 h-[500px] flex flex-col shadow-sm" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
          <h3 class="font-bold font-headline mb-6" style="color: #131722;">Revenue by Category</h3>
          <div class="flex-1 space-y-5 overflow-y-auto pr-2 no-scrollbar">
            <template v-if="revenueByCategory && revenueByCategory.length">
              <div v-for="(cat, idx) in revenueByCategory" :key="cat.kategori">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium truncate max-w-[65%]" style="color: #1F2937;">{{ cat.kategori }}</span>
                  <span class="text-sm font-bold" style="color: #131722;">{{ cat.percent }}%</span>
                </div>
                <div class="w-full rounded-full h-1.5" style="background-color: #E2E8F0;">
                  <div
                    class="h-1.5 rounded-full transition-all duration-700 ease-out"
                    :style="{ width: cat.percent + '%', background: getCategoryColor(cat.kategori) }"
                  ></div>
                </div>
                <p class="text-xs mt-1 text-right" style="color: #64748B;">{{ formatRupiah(cat.revenue) }}</p>
              </div>
            </template>
            <div v-else class="text-sm text-center py-8" style="color: #64748B;">
              Belum ada data penjualan per kategori.
            </div>
          </div>
        </div>

      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="rounded-lg p-6 shadow-sm flex flex-col" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
          <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold uppercase font-headline tracking-wider text-sm" style="color: #131722;">Top 10 Stok Terbanyak</h3>
            <span class="material-symbols-outlined text-[20px] text-blue-400">inventory_2</span>
          </div>
          <div class="space-y-3 overflow-y-auto no-scrollbar" style="max-height:320px;">
            <template v-if="topStok && topStok.length">
              <div v-for="(item, idx) in topStok" :key="item.nama" class="flex items-center gap-3 px-2 py-1 rounded-md transition-all duration-200" style="cursor: default;" onmouseover="this.style.backgroundColor='#F8F9FA'" onmouseout="this.style.backgroundColor=''">
                <span class="text-xs font-bold w-5 text-right shrink-0" style="color: #64748B;">{{ idx + 1 }}</span>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium truncate" style="color: #1F2937;">{{ item.nama }}</span>
                    <span class="text-xs font-semibold ml-2 shrink-0 text-blue-500">{{ formatCount(item.jumlah) }}</span>
                  </div>
                  <div class="w-full rounded-full h-1.5" style="background-color: #E2E8F0;">
                    <div class="bg-[#93C5FD] h-1.5 rounded-full transition-all duration-700 ease-out" :style="{ width: item.width_percent + '%' }"></div>
                  </div>
                </div>
              </div>
            </template>
            <div v-else class="text-sm text-center py-6" style="color: #64748B;">Belum ada data stok.</div>
          </div>
        </div>

        <div class="rounded-lg p-6 shadow-sm flex flex-col" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
          <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold uppercase font-headline tracking-wider text-sm" style="color: #131722;">Top 10 Barang Terjual</h3>
            <span class="material-symbols-outlined text-[20px] text-[#f59e0b]">sell</span>
          </div>
          <div class="space-y-3 overflow-y-auto no-scrollbar" style="max-height:320px;">
            <template v-if="topTerjual && topTerjual.length">
              <div v-for="(item, idx) in topTerjual" :key="item.nama" class="flex items-center gap-3 px-2 py-1 rounded-md transition-all duration-200" style="cursor: default;" onmouseover="this.style.backgroundColor='#F8F9FA'" onmouseout="this.style.backgroundColor=''">
                <span class="text-xs font-bold w-5 text-right shrink-0" style="color: #64748B;">{{ idx + 1 }}</span>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium truncate" style="color: #1F2937;">{{ item.nama }}</span>
                    <span class="text-xs font-semibold ml-2 shrink-0 text-[#d97706]">{{ formatCount(item.jumlah) }}</span>
                  </div>
                  <div class="w-full rounded-full h-1.5" style="background-color: #E2E8F0;">
                    <div class="bg-[#F59E0B] h-1.5 rounded-full transition-all duration-700 ease-out" :style="{ width: item.width_percent + '%' }"></div>
                  </div>
                </div>
              </div>
            </template>
            <div v-else class="text-sm text-center py-6" style="color: #64748B;">Belum ada data penjualan.</div>
          </div>
        </div>

      </div>

      <div class="flex flex-col gap-4">
        <h2 class="text-2xl font-light tracking-tight font-display" style="color: #131722;">Analisis Tren Pesanan</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <div class="lg:col-span-2 rounded-lg shadow-sm flex flex-col h-[400px]" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="p-6 pb-2 flex justify-between items-center" style="border-bottom: 1px solid #E2E8F0;">
              <div class="flex items-center gap-3">
                <h3 class="font-bold font-headline uppercase tracking-wider text-sm" style="color: #131722;">Trend Pesanan</h3>
                <span
                  v-if="pesananTrend.trendPercent != null"
                  class="flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :style="{
                    color: trendColor(pesananTrend.trendPercent),
                    backgroundColor: trendBg(pesananTrend.trendPercent)
                  }"
                >
                  <span class="material-symbols-outlined text-[14px] mr-1">
                    {{ trendIcon(pesananTrend.trendPercent) }}
                  </span>
                  {{ (pesananTrend.trendPercent ?? 0) > 0 ? '+' : '' }}{{ formatPercent(pesananTrend.trendPercent) }}%
                </span>
              </div>
              <div class="flex rounded-DEFAULT p-1 overflow-x-auto no-scrollbar gap-1" style="background-color: #ECEFF1;">
                <button
                  v-for="p in periods" :key="p"
                  @click="changePeriod(p)"
                  class="px-2 py-1 text-[10px] font-medium rounded-DEFAULT transition-all duration-300 ease-out transform hover:scale-105 active:scale-95 whitespace-nowrap"
                  :class="activePeriod === p
                    ? 'font-bold shadow-sm bg-[#FFFFFF]'
                    : 'hover:text-blue-600 hover:bg-[#F8F9FA]'"
                  :style="activePeriod === p ? { color: '#2563eb', boxShadow: '0 0 0 1px #93c5fd' } : { color: '#64748B' }"
                >{{ p }}</button>
              </div>
            </div>
            <div class="p-6 flex-1 relative flex items-end justify-between pl-16 pr-6 pb-12 pt-8">
              <div class="absolute inset-0 p-6 pb-12 pl-16 pr-6 pointer-events-none flex flex-col justify-between">
                <div v-for="i in 4" :key="i" class="w-full" style="border-top: 1px solid #E2E8F0;"></div>
                <div class="w-full" style="border-top: 1px solid #E2E8F0;"></div>
              </div>
              <template v-if="pesananTrend.buckets && pesananTrend.buckets.length">
                <div
                  v-for="bucket in pesananTrend.buckets" :key="bucket.label"
                  class="relative z-10 flex flex-col items-center group h-full justify-end"
                  style="flex: 1;"
                >
                  <div
                    class="w-full max-w-[28px] shadow-sm relative cursor-pointer transition-colors"
                    :class="bucket.height_percent === 100   ? 'bg-blue-600'   : 'bg-blue-300 group-hover:bg-blue-500'"
                    :style="{ height: Math.max(bucket.height_percent, 2) + '%', opacity: 1 }"
                  >
                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 shadow-lg rounded-DEFAULT p-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 whitespace-nowrap" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                      <p class="text-[10px] font-label uppercase mb-0.5 text-center" style="color: #64748B;">{{ bucket.label }}</p>
                      <p class="text-xs font-bold text-center" style="color: #131722;">{{ formatCount(bucket.count) }} pesanan</p>
                    </div>
                  </div>
                  <div class="absolute -bottom-7 text-[10px] font-label text-center w-full truncate" style="color: #64748B;">{{ bucket.label }}</div>
                </div>
              </template>
              <div v-else class="w-full h-full flex items-center justify-center text-sm" style="color: #64748B;">
                Belum ada data pesanan.
              </div>
              <div class="absolute left-2 top-8 bottom-12 w-12 pr-2 flex flex-col justify-between text-[10px] font-label pointer-events-none text-right" style="color: #64748B;">
                <span v-for="label in pesananTrend.yAxisLabels" :key="label" class="truncate" :title="label">{{ label }}</span>
              </div>
            </div>
          </div>

          <div class="rounded-lg p-6 shadow-sm flex flex-col h-[400px]" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="flex justify-between items-center mb-6">
              <h3 class="font-bold uppercase font-headline tracking-wider text-sm" style="color: #131722;">Top 5 Pesanan</h3>
              <span class="material-symbols-outlined text-[20px] text-blue-500 ">shopping_cart</span>
            </div>
            <div class="space-y-4 overflow-y-auto no-scrollbar flex-1">
              <template v-if="topPesanan && topPesanan.length">
                <div 
                    v-for="(item, idx) in topPesanan" 
                    :key="item.nama" 
                    class="flex items-center gap-3 px-2 py-1 rounded-md transition-all duration-200"
                    style="cursor: default;" onmouseover="this.style.backgroundColor='#F8F9FA'" onmouseout="this.style.backgroundColor=''"
                  >
                  <span class="text-xs font-bold w-5 text-right shrink-0" style="color: #64748B;">{{ idx + 1 }}</span>
                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center mb-1">
                      <span class="text-sm font-medium truncate" style="color: #1F2937;">{{ item.nama }}</span>
                      <span class="text-sm font-semibold text-blue-600 ml-2 shrink-0">{{ formatCount(item.jumlah) }}</span>
                    </div>
                    <div class="w-full rounded-full h-1.5" style="background-color: #E2E8F0;">
                      <div class="bg-[#60A5FA] h-1.5 rounded-full transition-all duration-700 ease-out" :style="{ width: item.width_percent + '%' }"></div>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else class="text-sm text-center py-6" style="color: #64748B;">Belum ada data pesanan.</div>
            </div>
          </div>

        </div>
      </div>

      <div class="flex flex-col gap-4">
        <h2 class="text-2xl font-light tracking-tight font-display" style="color: #131722;">Analisis Tren Servis</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <div class="lg:col-span-2 rounded-lg shadow-sm flex flex-col h-[400px]" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="p-6 pb-2 flex justify-between items-center" style="border-bottom: 1px solid #E2E8F0;">
              <div class="flex items-center gap-3">
                <h3 class="font-bold font-headline uppercase tracking-wider text-sm" style="color: #131722;">Trend Servis</h3>
                <span
                  v-if="servisTrend.trendPercent != null"
                  class="flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :style="{
                    color: trendColor(servisTrend.trendPercent),
                    backgroundColor: trendBg(servisTrend.trendPercent)
                  }"
                >
                  <span class="material-symbols-outlined text-[14px] mr-1">
                    {{ trendIcon(servisTrend.trendPercent) }}
                  </span>
                  {{ (servisTrend.trendPercent ?? 0) > 0 ? '+' : '' }}{{ formatPercent(servisTrend.trendPercent) }}%
                </span>
              </div>
              <div class="flex rounded-DEFAULT p-1 overflow-x-auto no-scrollbar gap-1" style="background-color: #ECEFF1;">
                <button
                  v-for="p in periods" :key="p"
                  @click="changePeriod(p)"
                  class="px-2 py-1 text-[10px] font-medium rounded-DEFAULT transition-all duration-300 ease-out transform hover:scale-105 active:scale-95 whitespace-nowrap"
                  :class="activePeriod === p
                    ? 'font-bold shadow-sm bg-[#FFFFFF]'
                    : 'hover:text-purple-600 hover:bg-[#F8F9FA]'"
                  :style="activePeriod === p ? { color: '#7c3aed', boxShadow: '0 0 0 1px #c4b5fd' } : { color: '#64748B' }"
                >{{ p }}</button>
              </div>
            </div>
            <div class="p-6 flex-1 relative flex items-end justify-between pl-16 pr-6 pb-12 pt-8">
              <div class="absolute inset-0 p-6 pb-12 pl-16 pr-6 pointer-events-none flex flex-col justify-between">
                <div v-for="i in 4" :key="i" class="w-full" style="border-top: 1px solid #E2E8F0;"></div>
                <div class="w-full" style="border-top: 1px solid #E2E8F0;"></div>
              </div>
              <template v-if="servisTrend.buckets && servisTrend.buckets.length">
                <div
                  v-for="bucket in servisTrend.buckets" :key="bucket.label"
                  class="relative z-10 flex flex-col items-center group h-full justify-end"
                  style="flex: 1;"
                >
                  <div
                    class="w-full max-w-[28px]  shadow-sm relative cursor-pointer transition-colors"
                    :class="bucket.height_percent === 100   ? 'bg-purple-600'   : 'bg-purple-300 group-hover:bg-purple-500'"
                    :style="{ height: Math.max(bucket.height_percent, 2) + '%', opacity: 1 }"
                  >
                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 shadow-lg rounded-DEFAULT p-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 whitespace-nowrap" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                      <p class="text-[10px] font-label uppercase mb-0.5 text-center" style="color: #64748B;">{{ bucket.label }}</p>
                      <p class="text-xs font-bold text-center" style="color: #131722;">{{ formatCount(bucket.count) }} servis</p>
                    </div>
                  </div>
                  <div class="absolute -bottom-7 text-[10px] font-label text-center w-full truncate" style="color: #64748B;">{{ bucket.label }}</div>
                </div>
              </template>
              <div v-else class="w-full h-full flex items-center justify-center text-sm" style="color: #64748B;">
                Belum ada data servis.
              </div>
              <div class="absolute left-2 top-8 bottom-12 w-12 pr-2 flex flex-col justify-between text-[10px] font-label pointer-events-none text-right" style="color: #64748B;">
                <span v-for="label in servisTrend.yAxisLabels" :key="label" class="truncate" :title="label">{{ label }}</span>
              </div>
            </div>
          </div>

          <div class="rounded-lg p-6 shadow-sm flex flex-col h-[400px]" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="flex justify-between items-center mb-6">
              <h3 class="font-bold uppercase font-headline tracking-wider text-sm" style="color: #131722;">Top 5 Servis</h3>
              <span class="material-symbols-outlined text-[20px] text-purple-500">build</span>
            </div>
            <div class="space-y-4 overflow-y-auto no-scrollbar flex-1">
              <template v-if="topServis && topServis.length">
                <div v-for="(item, idx) in topServis" :key="item.nama" class="flex items-center gap-3 px-2 py-1 rounded-md transition-all duration-200" style="cursor: default;" onmouseover="this.style.backgroundColor='#F8F9FA'" onmouseout="this.style.backgroundColor=''">
                  <span class="text-xs font-bold w-5 text-right shrink-0" style="color: #64748B;">{{ idx + 1 }}</span>
                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center mb-1">
                      <span class="text-sm font-medium truncate" style="color: #1F2937;">{{ item.nama }}</span>
                      <span class="text-sm font-semibold text-purple-600 ml-2 shrink-0">{{ formatCount(item.jumlah) }}</span>
                    </div>
                    <div class="w-full rounded-full h-1.5" style="background-color: #E2E8F0;">
                      <div class="bg-[#A78BFA] h-1.5 rounded-full transition-all duration-700 ease-out" :style="{ width: item.width_percent + '%' }"></div>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else class="text-sm text-center py-6" style="color: #64748B;">Belum ada data servis.</div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </main>
</div>
</template>



<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
