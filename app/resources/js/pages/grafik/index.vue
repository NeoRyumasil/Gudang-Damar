<template>
<div class="flex bg-surface text-on-surface min-h-screen">
  <main class="flex-1 min-h-screen w-full">
    <div class="pt-8 px-6 pb-12 max-w-7xl mx-auto space-y-8">

      <!-- ── Page Header ───────────────────────────────────────────────── -->
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
          <h2 class="text-3xl font-light tracking-tight text-primary font-display mb-1">Revenue Analytics</h2>
          <p class="text-on-surface-variant text-sm">Detailed financial insights for your warehouse operations.</p>
        </div>
      </div>

      <!-- ── Summary Cards ─────────────────────────────────────────────── -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card 1: Total Revenue -->
        <div class="bg-surface-container-lowest rounded-lg p-6 shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col justify-between h-36">
          <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-widest font-label">Total Revenue</span>
          <div>
            <div class="text-3xl font-light text-primary tracking-tight font-display">
              {{ formatRupiah(summaryCards.totalRevenue) }}
            </div>
            <div class="flex items-center mt-2">
              <span
                class="flex items-center px-2 py-0.5 rounded-full text-xs font-medium mr-2"
                :class="(summaryCards.revenueChangePercent ?? 0) >= 0
                  ? 'text-secondary bg-secondary-container/30'
                  : 'text-error bg-error-container/30'"
              >
                <span class="material-symbols-outlined text-[14px] mr-1">
                  {{ (summaryCards.revenueChangePercent ?? 0) >= 0 ? 'trending_up' : 'trending_down' }}
                </span>
                {{ (summaryCards.revenueChangePercent ?? 0) >= 0 ? '+' : '' }}{{ summaryCards.revenueChangePercent ?? 0 }}%
              </span>
              <span class="text-xs text-outline">vs bulan lalu</span>
            </div>
          </div>
        </div>

        <!-- Card 2: YTD Growth -->
        <div class="bg-surface-container-lowest rounded-lg p-6 shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col justify-between h-36">
          <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-widest font-label">YTD Growth</span>
          <div>
            <div class="text-3xl font-light text-primary tracking-tight font-display">
              {{ (summaryCards.ytdGrowth ?? 0) >= 0 ? '+' : '' }}{{ summaryCards.ytdGrowth ?? 0 }}%
            </div>
            <div class="flex items-center mt-2">
              <span class="text-xs text-outline">vs periode yang sama tahun lalu</span>
            </div>
          </div>
        </div>

        <!-- Card 3: Peak Period -->
        <div class="bg-surface-container-lowest rounded-lg p-6 shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col justify-between h-36 relative overflow-hidden">
          <div class="absolute right-0 bottom-0 opacity-5 pointer-events-none">
            <span class="material-symbols-outlined text-[100px]">military_tech</span>
          </div>
          <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-widest font-label relative z-10">Peak Revenue Period</span>
          <div class="relative z-10">
            <div class="text-3xl font-light text-primary tracking-tight font-display">
              {{ summaryCards.peakPeriod || '—' }}
            </div>
            <div class="flex items-center mt-2">
              <span class="text-xs text-outline">{{ formatRupiah(summaryCards.peakRevenue) }} pendapatan tertinggi</span>
            </div>
          </div>
        </div>

      </div>

      <!-- ── Revenue Trend + Category ──────────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Bar Chart: Revenue Trend -->
        <div class="lg:col-span-2 bg-surface-container-lowest rounded-lg shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col h-[500px]">
          <div class="p-6 pb-2 border-b border-surface-variant flex justify-between items-center">
            <h3 class="font-bold text-primary font-headline">Revenue Trend Analysis</h3>
            <div class="flex bg-surface-container-high rounded-DEFAULT p-1 overflow-x-auto no-scrollbar">
              <button
                v-for="p in periods"
                :key="p"
                @click="changePeriod(p)"
                class="px-3 py-1 text-xs font-medium rounded-DEFAULT transition-colors whitespace-nowrap"
                :class="activePeriod === p
                  ? 'font-bold bg-surface-container-lowest text-primary shadow-sm'
                  : 'text-on-surface-variant hover:text-primary'"
              >{{ p }}</button>
            </div>
          </div>
          <div class="p-6 flex-1 relative flex items-end justify-between px-10 pb-12 pt-8">
            <!-- Grid Lines -->
            <div class="absolute inset-0 p-6 pb-12 pointer-events-none flex flex-col justify-between">
              <div v-for="i in 4" :key="i" class="border-t border-surface-container-high w-full"></div>
              <div class="border-t border-outline-variant/40 w-full"></div>
            </div>

            <!-- Bars -->
            <template v-if="revenueTrend.buckets && revenueTrend.buckets.length">
              <div
                v-for="bucket in revenueTrend.buckets"
                :key="bucket.label"
                class="relative z-10 flex flex-col items-center group h-full justify-end"
                style="flex: 1;"
              >
                <div
                  class="w-full max-w-[32px] rounded-t-sm shadow-sm relative cursor-pointer transition-colors"
                  :class="bucket.height_percent === 100 ? 'bg-secondary' : 'bg-primary-container group-hover:bg-primary'"
                  :style="{ height: Math.max(bucket.height_percent, 2) + '%', opacity: bucket.height_percent === 0 ? 0.3 : 1 }"
                >
                  <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-surface-container-lowest border border-outline-variant/20 shadow-lg rounded-DEFAULT p-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none w-32 z-50">
                    <p class="text-[10px] font-label uppercase text-outline mb-1 text-center">{{ bucket.label }}</p>
                    <p class="text-sm font-bold text-primary text-center">{{ formatRupiah(bucket.revenue) }}</p>
                  </div>
                </div>
                <div class="absolute -bottom-8 text-[10px] text-outline font-label text-center w-full truncate">{{ bucket.label }}</div>
              </div>
            </template>
            <div v-else class="w-full h-full flex items-center justify-center text-sm text-outline">
              Belum ada data revenue.
            </div>

            <!-- Y-Axis Labels -->
            <div class="absolute left-2 top-8 bottom-12 flex flex-col justify-between text-[10px] text-outline font-label pointer-events-none">
              <span v-for="label in revenueTrend.yAxisLabels" :key="label">{{ label }}</span>
            </div>
          </div>
        </div>

        <!-- Revenue by Category -->
        <div class="bg-surface-container-low rounded-lg p-6 h-[500px] flex flex-col">
          <h3 class="font-bold text-primary font-headline mb-6">Revenue by Category</h3>
          <div class="flex-1 space-y-5 overflow-y-auto pr-2 no-scrollbar">
            <template v-if="revenueByCategory && revenueByCategory.length">
              <div v-for="(cat, idx) in revenueByCategory" :key="cat.kategori">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-on-surface truncate max-w-[65%]">{{ cat.kategori }}</span>
                  <span class="text-sm font-bold text-primary">{{ cat.percent }}%</span>
                </div>
                <div class="w-full bg-surface-variant rounded-full h-1.5">
                  <div
                    class="h-1.5 rounded-full transition-all"
                    :style="{ width: cat.percent + '%', background: categoryColors[idx % categoryColors.length] }"
                  ></div>
                </div>
                <p class="text-xs text-outline mt-1 text-right">{{ formatRupiah(cat.revenue) }}</p>
              </div>
            </template>
            <div v-else class="text-sm text-center py-8 text-outline">
              Belum ada data penjualan per kategori.
            </div>
          </div>
        </div>

      </div>

      <!-- ── Top Stok & Top Terjual ─────────────────────────────────────── -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Top Stok -->
        <div class="bg-surface-container-lowest rounded-lg p-6 shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col">
          <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-primary uppercase font-headline tracking-wider text-sm">Top 10 Stok Terbanyak</h3>
            <span class="material-symbols-outlined text-[20px] text-outline">inventory_2</span>
          </div>
          <div class="space-y-3 overflow-y-auto no-scrollbar" style="max-height:320px;">
            <template v-if="topStok && topStok.length">
              <div v-for="(item, idx) in topStok" :key="item.nama" class="flex items-center gap-3">
                <span class="text-xs font-bold w-5 text-right shrink-0 text-primary">{{ idx + 1 }}</span>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-on-surface truncate">{{ item.nama }}</span>
                    <span class="text-xs font-semibold ml-2 shrink-0 text-primary">{{ formatCount(item.jumlah) }}</span>
                  </div>
                  <div class="w-full bg-surface-container-high rounded-full h-1.5">
                    <div class="bg-primary h-1.5 rounded-full" :style="{ width: item.width_percent + '%' }"></div>
                  </div>
                </div>
              </div>
            </template>
            <div v-else class="text-sm text-center py-6 text-outline">Belum ada data stok.</div>
          </div>
        </div>

        <!-- Top Terjual -->
        <div class="bg-surface-container-lowest rounded-lg p-6 shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col">
          <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-primary uppercase font-headline tracking-wider text-sm">Top 10 Barang Terjual</h3>
            <span class="material-symbols-outlined text-[20px] text-outline">sell</span>
          </div>
          <div class="space-y-3 overflow-y-auto no-scrollbar" style="max-height:320px;">
            <template v-if="topTerjual && topTerjual.length">
              <div v-for="(item, idx) in topTerjual" :key="item.nama" class="flex items-center gap-3">
                <span class="text-xs font-bold w-5 text-right shrink-0 text-secondary">{{ idx + 1 }}</span>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-on-surface truncate">{{ item.nama }}</span>
                    <span class="text-xs font-semibold ml-2 shrink-0 text-secondary">{{ formatCount(item.jumlah) }}</span>
                  </div>
                  <div class="w-full bg-surface-container-high rounded-full h-1.5">
                    <div class="bg-secondary h-1.5 rounded-full" :style="{ width: item.width_percent + '%' }"></div>
                  </div>
                </div>
              </div>
            </template>
            <div v-else class="text-sm text-center py-6 text-outline">Belum ada data penjualan.</div>
          </div>
        </div>

      </div>

      <!-- ── Analisis Tren Pesanan ───────────────────────────────────────── -->
      <div class="flex flex-col gap-4">
        <h2 class="text-2xl font-light tracking-tight text-primary font-display">Analisis Tren Pesanan</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- Bar Chart Pesanan -->
          <div class="lg:col-span-2 bg-surface-container-lowest rounded-lg shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col h-[400px]">
            <div class="p-6 pb-2 border-b border-surface-variant flex justify-between items-center">
              <h3 class="font-bold text-primary font-headline uppercase tracking-wider text-sm">Trend Pesanan</h3>
              <div class="flex bg-surface-container-high rounded-DEFAULT p-1 overflow-x-auto no-scrollbar">
                <button
                  v-for="p in periods" :key="p"
                  @click="changePeriod(p)"
                  class="px-2 py-1 text-[10px] font-medium rounded-DEFAULT transition-colors whitespace-nowrap"
                  :class="activePeriod === p
                    ? 'font-bold bg-surface-container-lowest text-primary shadow-sm'
                    : 'text-on-surface-variant hover:text-primary'"
                >{{ p }}</button>
              </div>
            </div>
            <div class="p-6 flex-1 relative flex items-end justify-between px-10 pb-12 pt-8">
              <div class="absolute inset-0 p-6 pb-12 pointer-events-none flex flex-col justify-between">
                <div v-for="i in 4" :key="i" class="border-t border-surface-container-high w-full"></div>
                <div class="border-t border-outline-variant/40 w-full"></div>
              </div>
              <template v-if="pesananTrend.buckets && pesananTrend.buckets.length">
                <div
                  v-for="bucket in pesananTrend.buckets" :key="bucket.label"
                  class="relative z-10 flex flex-col items-center group h-full justify-end"
                  style="flex: 1;"
                >
                  <div
                    class="w-full max-w-[28px] bg-primary-container group-hover:bg-primary rounded-t-sm shadow-sm relative cursor-pointer transition-colors"
                    :style="{ height: Math.max(bucket.height_percent, 2) + '%', opacity: bucket.height_percent === 0 ? 0.3 : 1 }"
                  >
                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-surface-container-lowest border border-outline-variant/20 shadow-lg rounded-DEFAULT p-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 whitespace-nowrap">
                      <p class="text-[10px] font-label uppercase text-outline mb-0.5 text-center">{{ bucket.label }}</p>
                      <p class="text-xs font-bold text-primary text-center">{{ bucket.count }} pesanan</p>
                    </div>
                  </div>
                  <div class="absolute -bottom-7 text-[10px] text-outline font-label text-center w-full truncate">{{ bucket.label }}</div>
                </div>
              </template>
              <div v-else class="w-full h-full flex items-center justify-center text-sm text-outline">
                Belum ada data pesanan.
              </div>
              <div class="absolute left-2 top-8 bottom-12 flex flex-col justify-between text-[10px] text-outline font-label pointer-events-none">
                <span v-for="label in pesananTrend.yAxisLabels" :key="label">{{ label }}</span>
              </div>
            </div>
          </div>

          <!-- Top 5 Pesanan -->
          <div class="bg-surface-container-lowest rounded-lg p-6 shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col h-[400px]">
            <div class="flex justify-between items-center mb-6">
              <h3 class="font-bold text-primary uppercase font-headline tracking-wider text-sm">Top 5 Pesanan</h3>
              <span class="material-symbols-outlined text-[20px] text-outline">shopping_cart</span>
            </div>
            <div class="space-y-4 overflow-y-auto no-scrollbar flex-1">
              <template v-if="topPesanan && topPesanan.length">
                <div v-for="(item, idx) in topPesanan" :key="item.nama" class="flex items-center gap-3">
                  <span class="text-xs font-bold w-5 text-right shrink-0 text-primary">{{ idx + 1 }}</span>
                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center mb-1">
                      <span class="text-sm font-medium text-on-surface truncate">{{ item.nama }}</span>
                      <span class="text-sm font-medium text-on-surface ml-2 shrink-0">{{ formatCount(item.jumlah) }}</span>
                    </div>
                    <div class="w-full bg-surface-container-high rounded-full h-1.5">
                      <div class="bg-primary h-1.5 rounded-full" :style="{ width: item.width_percent + '%' }"></div>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else class="text-sm text-center py-6 text-outline">Belum ada data pesanan.</div>
            </div>
          </div>

        </div>
      </div>

      <!-- ── Analisis Tren Servis ────────────────────────────────────────── -->
      <div class="flex flex-col gap-4">
        <h2 class="text-2xl font-light tracking-tight text-primary font-display">Analisis Tren Servis</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- Bar Chart Servis -->
          <div class="lg:col-span-2 bg-surface-container-lowest rounded-lg shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col h-[400px]">
            <div class="p-6 pb-2 border-b border-surface-variant flex justify-between items-center">
              <h3 class="font-bold text-primary font-headline uppercase tracking-wider text-sm">Trend Servis</h3>
              <div class="flex bg-surface-container-high rounded-DEFAULT p-1 overflow-x-auto no-scrollbar">
                <button
                  v-for="p in periods" :key="p"
                  @click="changePeriod(p)"
                  class="px-2 py-1 text-[10px] font-medium rounded-DEFAULT transition-colors whitespace-nowrap"
                  :class="activePeriod === p
                    ? 'font-bold bg-surface-container-lowest text-primary shadow-sm'
                    : 'text-on-surface-variant hover:text-primary'"
                >{{ p }}</button>
              </div>
            </div>
            <div class="p-6 flex-1 relative flex items-end justify-between px-10 pb-12 pt-8">
              <div class="absolute inset-0 p-6 pb-12 pointer-events-none flex flex-col justify-between">
                <div v-for="i in 4" :key="i" class="border-t border-surface-container-high w-full"></div>
                <div class="border-t border-outline-variant/40 w-full"></div>
              </div>
              <template v-if="servisTrend.buckets && servisTrend.buckets.length">
                <div
                  v-for="bucket in servisTrend.buckets" :key="bucket.label"
                  class="relative z-10 flex flex-col items-center group h-full justify-end"
                  style="flex: 1;"
                >
                  <div
                    class="w-full max-w-[28px] bg-primary-container group-hover:bg-primary rounded-t-sm shadow-sm relative cursor-pointer transition-colors"
                    :style="{ height: Math.max(bucket.height_percent, 2) + '%', opacity: bucket.height_percent === 0 ? 0.3 : 1 }"
                  >
                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-surface-container-lowest border border-outline-variant/20 shadow-lg rounded-DEFAULT p-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 whitespace-nowrap">
                      <p class="text-[10px] font-label uppercase text-outline mb-0.5 text-center">{{ bucket.label }}</p>
                      <p class="text-xs font-bold text-primary text-center">{{ bucket.count }} servis</p>
                    </div>
                  </div>
                  <div class="absolute -bottom-7 text-[10px] text-outline font-label text-center w-full truncate">{{ bucket.label }}</div>
                </div>
              </template>
              <div v-else class="w-full h-full flex items-center justify-center text-sm text-outline">
                Belum ada data servis.
              </div>
              <div class="absolute left-2 top-8 bottom-12 flex flex-col justify-between text-[10px] text-outline font-label pointer-events-none">
                <span v-for="label in servisTrend.yAxisLabels" :key="label">{{ label }}</span>
              </div>
            </div>
          </div>

          <!-- Top 5 Servis -->
          <div class="bg-surface-container-lowest rounded-lg p-6 shadow-[0_4px_20px_rgba(0,30,64,0.03)] border border-outline-variant/15 flex flex-col h-[400px]">
            <div class="flex justify-between items-center mb-6">
              <h3 class="font-bold text-primary uppercase font-headline tracking-wider text-sm">Top 5 Servis</h3>
              <span class="material-symbols-outlined text-[20px] text-outline">build</span>
            </div>
            <div class="space-y-4 overflow-y-auto no-scrollbar flex-1">
              <template v-if="topServis && topServis.length">
                <div v-for="(item, idx) in topServis" :key="item.nama" class="flex items-center gap-3">
                  <span class="text-xs font-bold w-5 text-right shrink-0 text-primary">{{ idx + 1 }}</span>
                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center mb-1">
                      <span class="text-sm font-medium text-on-surface truncate">{{ item.nama }}</span>
                      <span class="text-sm font-medium text-on-surface ml-2 shrink-0">{{ formatCount(item.jumlah) }}</span>
                    </div>
                    <div class="w-full bg-surface-container-high rounded-full h-1.5">
                      <div class="bg-primary h-1.5 rounded-full" :style="{ width: item.width_percent + '%' }"></div>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else class="text-sm text-center py-6 text-outline">Belum ada data servis.</div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </main>
</div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

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

// Warna untuk bar revenue by category (pakai hex fallback karena CSS var tidak bisa di-interpolate di :style)
const categoryColors = [
  '#1a3a6b', '#2d7a4f', '#7b3f00', '#4a0080', '#005f73', '#9e2a2b'
]

// Ganti period → Inertia request baru ke /grafik?period=X
function changePeriod(period) {
  router.get('/grafik', { period }, {
    preserveState:  true,
    preserveScroll: true,
    replace:        true,
  })
}

// Format ke Rupiah singkat: 1.5M, 850 Jt, dll.
function formatRupiah(value) {
  if (!value && value !== 0) return 'Rp 0'
  if (value >= 1_000_000_000) return 'Rp ' + (value / 1_000_000_000).toFixed(2) + ' M'
  if (value >= 1_000_000)     return 'Rp ' + (value / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' Jt'
  return 'Rp ' + value.toLocaleString('id-ID')
}

// Format ke satuan singkat: 1.5K, 2.3M, dll.
function formatCount(value) {
  if (!value) return '0'
  if (value >= 1_000_000) return (value / 1_000_000).toFixed(1) + 'M'
  if (value >= 1_000)     return (value / 1_000).toFixed(1) + 'K'
  return value.toString()
}
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>