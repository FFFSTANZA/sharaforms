<template>
  <div v-if="workspace" class="flex flex-col min-h-screen px-4 sm:px-10 py-8">
    <!-- Tab: Dashboard -->
    <div v-if="activeTab === 'dashboard'" class="w-full">
      <!-- Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">
        <div>
          <span class="grad-kicker w-8 h-1 rounded-full block mb-3"></span>
          <h1 class="text-[28px] font-bold text-[#1D1F24] tracking-tight">Dashboard</h1>
          <p class="text-[13px] text-[#6E7278] font-medium mt-1">A snapshot of your forms' performance</p>
        </div>
        <div class="flex items-center gap-4">
          <div ref="dateDropdownRef" class="relative">
            <button
              class="btn-ghost flex items-center gap-2 border border-[#DEE1E7] rounded-xl px-4 py-3.5 text-xs font-semibold text-[#565A62] shadow-[0_1px_2px_rgba(23,25,35,0.04)]"
              @click.stop="showDateDropdown = !showDateDropdown"
            >
              <i class="fa-solid fa-calendar-day text-[10px] text-[#EA6676]"></i>
              {{ selectedDateLabel }} <i class="fa-solid fa-chevron-down text-[9px] text-[#A7ABB2]"></i>
            </button>
            <div
              v-if="showDateDropdown"
              class="absolute right-0 top-full mt-2 bg-white border border-[#E6E8EE] rounded-xl shadow-[0_12px_40px_-12px_rgba(23,25,35,0.2)] z-50 py-1.5 min-w-[160px]"
            >
              <button
                v-for="option in dateOptions"
                :key="option.value"
                class="w-full text-left px-4 py-2.5 text-xs font-medium transition-colors"
                :class="selectedDays === option.value ? 'text-[#EA6676] bg-[#FDE8EB]' : 'text-[#565A62] hover:bg-[#F7F8FA]'"
                @click="selectDateRange(option.value)"
              >
                {{ option.label }}
              </button>
            </div>
          </div>
          <NuxtLink
            v-if="!workspace?.is_readonly"
            :to="{ name: 'forms-create' }"
            class="btn-primary text-white px-6 py-3.5 rounded-xl flex items-center gap-2.5 text-sm font-semibold shadow-sm"
          >
            <i class="fa-solid fa-plus text-xs"></i>
            Create Form
          </NuxtLink>
        </div>
      </div>

      <!-- KPI Strip -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-10">
        <!-- Total Views -->
        <div
          class="bg-[#E4F4F8] rounded-2xl border border-[#0891b2]/30 p-6 hover:-translate-y-0.5 hover:border-[#0891b2]/50 transition-all shadow-[inset_0_1px_0_0_#FFFFFF,0_1px_2px_rgba(23,25,35,0.04),0_16px_32px_-20px_rgba(8,145,178,0.35)]"
        >
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
              <span class="tile-view w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-regular fa-eye text-white text-sm"></i>
              </span>
              <p class="text-sm font-medium text-[#565A62] truncate">Total views</p>
            </div>
            <div class="flex flex-col items-end shrink-0">
              <span
                class="inline-flex items-center text-xs font-semibold tabular-nums"
                :class="viewsTrend.direction === 'up' ? 'text-[#0e7a46]' : viewsTrend.direction === 'down' ? 'text-[#dc2626]' : 'text-[#8E9198]'"
              >
                <i
                  class="text-[9px] mr-0.5"
                  :class="viewsTrend.direction === 'up' ? 'fa-solid fa-arrow-trend-up' : viewsTrend.direction === 'down' ? 'fa-solid fa-arrow-trend-down' : 'fa-solid fa-minus'"
                ></i>{{ viewsTrend.percentage }}%
              </span>
              <span class="text-[11px] text-[#8E9198] mt-1">vs previous</span>
            </div>
          </div>
          <p class="text-[32px] font-bold leading-none tracking-tight text-[#1D1F24] tabular-nums mt-6 whitespace-nowrap">
            {{ formatNumberWithCommas(totalViews) }}
          </p>
          <svg class="mt-6 w-full h-9" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none" aria-hidden="true">
            <defs>
              <linearGradient id="sp-views" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#0891b2" stop-opacity="0.22" />
                <stop offset="1" stop-color="#0891b2" stop-opacity="0" />
              </linearGradient>
            </defs>
            <path d="M0 26 C10 22, 18 14, 28 16 S46 8, 52 10 S66 26, 74 20 S94 12, 100 6 L100 32 L0 32 Z" fill="url(#sp-views)" />
            <path d="M0 26 C10 22, 18 14, 28 16 S46 8, 52 10 S66 26, 74 20 S94 12, 100 6" stroke="#0891b2" stroke-width="2" stroke-linecap="round" />
            <circle cx="100" cy="6" r="2.5" fill="#0891b2" stroke="#FFFFFF" stroke-width="1.5" />
          </svg>
        </div>

        <!-- Total Responses -->
        <div
          class="bg-[#FDF6EB] rounded-2xl border border-[#d97706]/30 p-6 hover:-translate-y-0.5 hover:border-[#d97706]/50 transition-all shadow-[inset_0_1px_0_0_#FFFFFF,0_1px_2px_rgba(23,25,35,0.04),0_16px_32px_-20px_rgba(217,119,6,0.35)]"
        >
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
              <span class="tile-resp w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-regular fa-file-lines text-white text-sm"></i>
              </span>
              <p class="text-sm font-medium text-[#565A62] truncate">Total responses</p>
            </div>
            <div class="flex flex-col items-end shrink-0">
              <span
                class="inline-flex items-center text-xs font-semibold tabular-nums"
                :class="submissionsTrend.direction === 'up' ? 'text-[#0e7a46]' : submissionsTrend.direction === 'down' ? 'text-[#dc2626]' : 'text-[#8E9198]'"
              >
                <i
                  class="text-[9px] mr-0.5"
                  :class="submissionsTrend.direction === 'up' ? 'fa-solid fa-arrow-trend-up' : submissionsTrend.direction === 'down' ? 'fa-solid fa-arrow-trend-down' : 'fa-solid fa-minus'"
                ></i>{{ submissionsTrend.percentage }}%
              </span>
              <span class="text-[11px] text-[#8E9198] mt-1">vs previous</span>
            </div>
          </div>
          <p class="text-[32px] font-bold leading-none tracking-tight text-[#1D1F24] tabular-nums mt-6 whitespace-nowrap">
            {{ formatNumberWithCommas(totalResponses) }}
          </p>
          <svg class="mt-6 w-full h-9" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none" aria-hidden="true">
            <defs>
              <linearGradient id="sp-resp" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#d97706" stop-opacity="0.22" />
                <stop offset="1" stop-color="#d97706" stop-opacity="0" />
              </linearGradient>
            </defs>
            <path d="M0 22 C8 24, 16 18, 26 20 S44 10, 54 12 S70 26, 80 18 S94 8, 100 10 L100 32 L0 32 Z" fill="url(#sp-resp)" />
            <path d="M0 22 C8 24, 16 18, 26 20 S44 10, 54 12 S70 26, 80 18 S94 8, 100 10" stroke="#d97706" stroke-width="2" stroke-linecap="round" />
            <circle cx="100" cy="10" r="2.5" fill="#d97706" stroke="#FFFFFF" stroke-width="1.5" />
          </svg>
        </div>

        <!-- Conversion Rate -->
        <div
          class="bg-[#EEF0FD] rounded-2xl border border-[#6366f1]/30 p-6 hover:-translate-y-0.5 hover:border-[#6366f1]/50 transition-all shadow-[inset_0_1px_0_0_#FFFFFF,0_1px_2px_rgba(23,25,35,0.04),0_16px_32px_-20px_rgba(99,102,241,0.35)]"
        >
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
              <span class="tile-conv w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-chart-line text-white text-sm"></i>
              </span>
              <p class="text-sm font-medium text-[#565A62] truncate">Conversion rate</p>
            </div>
            <div class="flex flex-col items-end shrink-0">
              <span
                class="inline-flex items-center text-xs font-semibold tabular-nums"
                :class="conversionTrend.direction === 'up' ? 'text-[#0e7a46]' : conversionTrend.direction === 'down' ? 'text-[#dc2626]' : 'text-[#8E9198]'"
              >
                <i
                  class="text-[9px] mr-0.5"
                  :class="conversionTrend.direction === 'up' ? 'fa-solid fa-arrow-trend-up' : conversionTrend.direction === 'down' ? 'fa-solid fa-arrow-trend-down' : 'fa-solid fa-minus'"
                ></i>{{ conversionTrend.percentage }}%
              </span>
              <span class="text-[11px] text-[#8E9198] mt-1">vs previous</span>
            </div>
          </div>
          <p class="text-[32px] font-bold leading-none tracking-tight text-[#1D1F24] tabular-nums mt-6 whitespace-nowrap">
            {{ conversionRate.toFixed(1) }}%
          </p>
          <svg class="mt-6 w-full h-9" viewBox="0 0 100 32" preserveAspectRatio="none" fill="none" aria-hidden="true">
            <defs>
              <linearGradient id="sp-conv" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#6366f1" stop-opacity="0.22" />
                <stop offset="1" stop-color="#6366f1" stop-opacity="0" />
              </linearGradient>
            </defs>
            <path d="M0 24 C10 20, 18 26, 28 22 S46 12, 56 14 S72 24, 82 16 S94 8, 100 10 L100 32 L0 32 Z" fill="url(#sp-conv)" />
            <path d="M0 24 C10 20, 18 26, 28 22 S46 12, 56 14 S72 24, 82 16 S94 8, 100 10" stroke="#6366f1" stroke-width="2" stroke-linecap="round" />
            <circle cx="100" cy="10" r="2.5" fill="#6366f1" stroke="#FFFFFF" stroke-width="1.5" />
          </svg>
        </div>

        <!-- Live Forms -->
        <div
          class="bg-[#EFF8F1] rounded-2xl border border-[#16a34a]/30 p-6 hover:-translate-y-0.5 hover:border-[#16a34a]/50 transition-all shadow-[inset_0_1px_0_0_#FFFFFF,0_1px_2px_rgba(23,25,35,0.04),0_16px_32px_-20px_rgba(22,163,74,0.35)]"
        >
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
              <span class="tile-live w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-check text-white text-sm"></i>
              </span>
              <p class="text-sm font-medium text-[#565A62] truncate">Live forms</p>
            </div>
            <div class="flex flex-col items-end shrink-0">
              <span class="text-xs font-semibold text-[#0e7a46] tabular-nums">
                {{ liveFormsCount }} / {{ forms?.length || 0 }}
              </span>
              <span class="text-[11px] text-[#8E9198] mt-1">live now</span>
            </div>
          </div>
          <p class="text-[32px] font-bold leading-none tracking-tight text-[#1D1F24] tabular-nums mt-6 whitespace-nowrap">
            {{ liveFormsCount }}
          </p>
          <div class="mt-6 h-2 rounded-full bg-[#16a34a]/15 overflow-hidden" aria-hidden="true">
            <div class="h-full rounded-full bg-[#16a34a]" :style="{ width: liveFormsPercentage + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- Weekly Views + Top Performing Forms -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Weekly views chart -->
        <div
          class="hover-shadow lg:col-span-2 bg-white rounded-2xl border border-[#E6E8EE] p-6 shadow-[inset_0_1px_0_0_#FFFFFF,0_1px_2px_rgba(23,25,35,0.04),0_16px_40px_-20px_rgba(23,25,35,0.18)]"
        >
          <div class="flex items-center justify-between mb-8">
            <div>
              <h2 class="text-[15px] font-semibold text-[#1D1F24]">Weekly views</h2>
              <p class="text-xs text-[#8E9198] font-medium mt-0.5">Last {{ selectedDays }} days</p>
            </div>
            <div class="flex items-center gap-4">
              <span class="flex items-center gap-2 text-xs font-medium text-[#6E7278]">
                <span class="w-2 h-2 rounded-full bg-[#0891b2]"></span>
                <span class="text-[#383B41] tabular-nums font-semibold">{{ formatNumberWithCommas(totalViews) }}</span> views
              </span>
              <span class="text-[11px] font-medium text-[#8E9198]">vs {{ formatNumberWithCommas(previousViewsTotal) }} previous</span>
            </div>
          </div>

          <div class="relative pl-8">
            <div class="absolute left-0 inset-y-0 flex flex-col justify-between pointer-events-none w-7">
              <span class="text-[11px] font-medium text-[#A7ABB2] tabular-nums -translate-y-0.5">{{ Math.round(peakChartValue) }}</span>
              <span class="text-[11px] font-medium text-[#A7ABB2] tabular-nums -translate-y-0.5">{{ Math.round(peakChartValue * 0.8) }}</span>
              <span class="text-[11px] font-medium text-[#A7ABB2] tabular-nums -translate-y-0.5">{{ Math.round(peakChartValue * 0.6) }}</span>
              <span class="text-[11px] font-medium text-[#A7ABB2] tabular-nums -translate-y-0.5">{{ Math.round(peakChartValue * 0.4) }}</span>
              <span class="text-[11px] font-medium text-[#A7ABB2] tabular-nums -translate-y-0.5">{{ Math.round(peakChartValue * 0.2) }}</span>
              <span class="text-[11px] font-medium text-[#A7ABB2] tabular-nums -translate-y-0.5">0</span>
            </div>
            <div class="absolute left-7 right-0 inset-y-0 flex flex-col justify-between pointer-events-none">
              <div class="border-t border-dashed border-[#ECEEF2]/70"></div>
              <div class="border-t border-dashed border-[#ECEEF2]/70"></div>
              <div class="border-t border-dashed border-[#ECEEF2]/70"></div>
              <div class="border-t border-dashed border-[#ECEEF2]/70"></div>
              <div class="border-t border-dashed border-[#ECEEF2]/70"></div>
              <div class="border-t border-dashed border-[#ECEEF2]/70"></div>
            </div>
            <div class="relative flex items-end gap-3 h-[240px]">
              <div
                v-for="day in weeklyData"
                :key="day.label"
                class="relative flex-1 h-full flex items-end justify-center group cursor-pointer"
              >
                <div
                  class="chart-bar relative w-full max-w-[64px] rounded-t-lg"
                  :class="{ 'peak': day.peak }"
                  :style="{ height: day.height + '%' }"
                >
                  <span
                    class="absolute left-1/2 -translate-x-1/2 -top-2 -translate-y-full text-[11px] font-semibold tabular-nums border rounded-lg px-2 py-1 shadow-md opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-30"
                    :class="day.peak ? 'text-white bg-[#EA6676] border-[#EA6676]' : 'text-[#1D1F24] bg-white border-[#E6E8EE]'"
                  >
                    {{ day.count }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="flex gap-2 mt-3 pl-8">
            <span v-for="day in weeklyData" :key="'label-' + day.label" class="flex-1 text-center text-[11px] font-medium text-[#8E9198]">
              {{ day.label }}
            </span>
          </div>
          <div class="flex items-center justify-between mt-6 pt-5 border-t border-[#ECEEF2]">
            <p class="text-xs font-medium text-[#8E9198]">
              Peak day · <span class="text-[#565A62] font-semibold tabular-nums">{{ peakDayText }}</span>
            </p>
            <p class="text-xs font-medium text-[#8E9198]">
              Daily average · <span class="text-[#565A62] font-semibold tabular-nums">{{ dailyAverage }}</span>
            </p>
          </div>
        </div>

        <!-- Top performing forms -->
        <div
          class="hover-shadow bg-white rounded-2xl border border-[#E6E8EE] p-6 shadow-[inset_0_1px_0_0_#FFFFFF,0_1px_2px_rgba(23,25,35,0.04),0_16px_40px_-20px_rgba(23,25,35,0.18)]"
        >
          <div class="flex items-start justify-between mb-5">
            <div>
              <h2 class="text-[15px] font-semibold text-[#1D1F24]">Top performing forms</h2>
              <p class="text-xs text-[#8E9198] font-medium mt-1">Ranked by response volume</p>
            </div>
            <NuxtLink
              :to="{ name: 'home', query: { tab: 'forms' } }"
              class="cursor-pointer shrink-0 mt-0.5 inline-flex items-center gap-1.5 text-xs font-semibold text-[#565A62] hover:text-[#EA6676] transition-colors"
            >
              View all <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </NuxtLink>
          </div>

          <div v-if="topPerformingForms.length > 0" class="divide-y divide-[#ECEEF2]">
            <div
              v-for="(form, idx) in topPerformingForms"
              :key="'top-' + form.id"
              class="py-4 first:pt-0 last:pb-0 cursor-pointer group relative"
              @click="navigateToSubmissions(form)"
            >
              <div class="flex items-center gap-3">
                <span class="text-[11px] font-bold text-[#C7C9CE] tabular-nums w-5 shrink-0">
                  0{{ idx + 1 }}
                </span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="getFormIconBg(form)">
                  <i :class="getFormIcon(form)" class="text-sm"></i>
                </div>
                <p class="flex-1 min-w-0 text-[13px] font-semibold text-[#1D1F24] truncate group-hover:text-[#EA6676] transition-colors">
                  {{ form.title }}
                </p>
                <span class="text-sm font-bold text-[#1D1F24] tabular-nums shrink-0">
                  {{ formatNumber(form.submissions_count) }}
                </span>
              </div>
              <div class="flex items-center gap-2.5 mt-3 ml-[68px]">
                <div class="h-1.5 flex-1 rounded-full bg-[#ECEEF2] overflow-hidden">
                  <div class="h-full rounded-full bg-[#0891b2]" :style="{ width: getProgressWidth(form) + '%' }"></div>
                </div>
                <span class="text-[11px] font-semibold text-[#8E9198] tabular-nums w-9 text-right shrink-0">
                  {{ getProgressWidth(form) }}%
                </span>
              </div>
            </div>
          </div>
          <div v-else class="flex flex-col items-center justify-center h-48 text-center text-neutral-400">
            <i class="fa-solid fa-folder-open text-3xl mb-3 text-neutral-300"></i>
            <p class="text-xs font-semibold">No performance data yet</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab: My Forms -->
    <div v-else class="w-full">
      <!-- Top Bar / Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <span class="grad-kicker w-8 h-1 rounded-full block mb-3"></span>
          <h1 class="text-[28px] font-bold text-[#1D1F24] tracking-tight">My Forms</h1>
          <p class="text-[13px] text-[#6E7278] font-medium mt-1">{{ headerSubtitle }}</p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Search -->
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-[#A7ABB2] text-xs"></i>
            <input
              type="search"
              v-model="search"
              placeholder="Search forms..."
              class="bg-white border border-[#DEE1E7] rounded-xl py-3.5 pl-11 pr-5 w-72 text-sm text-[#1D1F24] placeholder:text-[#8E9198] focus:outline-none focus:ring-2 focus:ring-[#EA6676]/15 focus:border-[#EA6676]/35 transition-all shadow-[0_1px_2px_rgba(23,25,35,0.04),0_8px_20px_-12px_rgba(23,25,35,0.14)]"
            />
          </div>

          <!-- Tags filter dropdown -->
          <USelectMenu
            v-if="allTags.length > 0"
            v-model="selectedTags"
            :items="tagOptions"
            multiple
            placeholder="Tags"
            class="min-w-[120px]"
            color="neutral"
            variant="outline"
            size="md"
          />

          <!-- Clear Filters -->
          <UButton
            v-if="isFilteringForms"
            label="Clear"
            variant="ghost"
            color="neutral"
            size="sm"
            @click="clearFilters"
          />

          <NuxtLink
            v-if="!workspace?.is_readonly"
            :to="{ name: 'forms-create' }"
            class="btn-primary text-white px-6 py-3 rounded-xl flex items-center justify-center gap-2.5 text-sm font-semibold shadow-sm shrink-0"
          >
            <i class="fa-solid fa-plus text-xs"></i>
            Create Form
          </NuxtLink>
        </div>
      </div>

      <!-- View Toolbar -->
      <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
          <div class="view-toggle flex items-center gap-1 bg-white border border-[#DEE1E7] rounded-xl p-1.5 shadow-[0_1px_2px_rgba(23,25,35,0.04)]">
            <button
              @click="viewMode = 'grid'"
              class="w-9 h-8 rounded-lg flex items-center justify-center transition-all"
              :class="{ 'view-active': viewMode === 'grid' }"
              title="Grid view"
            >
              <i class="fa-solid fa-table-cells-large text-[13px]"></i>
            </button>
            <button
              @click="viewMode = 'list'"
              class="w-9 h-8 rounded-lg flex items-center justify-center transition-all"
              :class="{ 'view-active': viewMode === 'list' }"
              title="List view"
            >
              <i class="fa-solid fa-list text-[13px]"></i>
            </button>
          </div>
          <span class="text-xs font-medium text-[#8E9198] tabular-nums">{{ enrichedForms.length }} forms</span>
        </div>
        <div class="flex items-center gap-2.5">
          <!-- Filter Dropdown -->
          <div ref="filterDropdownRef" class="relative">
            <button
              class="btn-ghost flex items-center gap-2 border border-[#DEE1E7] rounded-xl px-4 py-3.5 text-xs font-semibold text-[#565A62] shadow-[0_1px_2px_rgba(23,25,35,0.04)]"
              @click.stop="showFilterDropdown = !showFilterDropdown"
            >
              <i class="fa-solid fa-sliders text-[10px] text-[#EA6676]"></i> Filter
              <span v-if="statusFilter !== 'all'" class="ml-1 w-1.5 h-1.5 rounded-full bg-[#EA6676]"></span>
            </button>
            <div
              v-if="showFilterDropdown"
              class="absolute right-0 top-full mt-2 bg-white border border-[#E6E8EE] rounded-xl shadow-[0_12px_40px_-12px_rgba(23,25,35,0.2)] z-50 py-1.5 min-w-[160px]"
            >
              <button
                v-for="option in statusFilterOptions"
                :key="option.value"
                class="w-full text-left px-4 py-2.5 text-xs font-medium transition-colors"
                :class="statusFilter === option.value ? 'text-[#EA6676] bg-[#FDE8EB]' : 'text-[#565A62] hover:bg-[#F7F8FA]'"
                @click="selectStatusFilter(option.value)"
              >
                {{ option.label }}
              </button>
            </div>
          </div>

          <!-- Sort Dropdown -->
          <div ref="sortDropdownRef" class="relative">
            <button
              class="btn-ghost flex items-center gap-2 border border-[#DEE1E7] rounded-xl px-4 py-3.5 text-xs font-semibold text-[#565A62] shadow-[0_1px_2px_rgba(23,25,35,0.04)]"
              @click.stop="showSortDropdown = !showSortDropdown"
            >
              <i class="fa-solid fa-arrow-up-wide-short text-[10px] text-[#EA6676]"></i> Sort
            </button>
            <div
              v-if="showSortDropdown"
              class="absolute right-0 top-full mt-2 bg-white border border-[#E6E8EE] rounded-xl shadow-[0_12px_40px_-12px_rgba(23,25,35,0.2)] z-50 py-1.5 min-w-[180px]"
            >
              <button
                v-for="option in sortOptions"
                :key="option.value"
                class="w-full text-left px-4 py-2.5 text-xs font-medium transition-colors flex items-center gap-2"
                :class="sortBy === option.value ? 'text-[#EA6676] bg-[#FDE8EB]' : 'text-[#565A62] hover:bg-[#F7F8FA]'"
                @click="selectSort(option.value)"
              >
                <span class="flex-1">{{ option.label }}</span>
                <i
                  v-if="sortBy === option.value"
                  class="fa-solid text-[8px]"
                  :class="sortDirection === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down'"
                ></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Area -->
      <ClientOnly>
        <div v-if="isFormsLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <FormCardSkeleton />
          <FormCardSkeleton />
          <FormCardSkeleton />
        </div>

        <div v-else-if="forms?.length === 0" class="text-center py-16 px-4 bg-white rounded-2xl border border-[#E6E8EE]">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FDE8EB] text-[#EA6676] mb-5">
            <i class="fa-solid fa-file-lines text-2xl"></i>
          </div>
          <h3 class="text-lg font-bold text-neutral-900">Create your first form</h3>
          <p class="mt-1 text-sm text-neutral-500 max-w-sm mx-auto">
            Get started by creating a new form to collect responses.
          </p>
          <NuxtLink
            :to="{ name: 'forms-create' }"
            class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl mt-6 font-semibold"
          >
            <i class="fa-solid fa-plus text-xs"></i> Create Form
          </NuxtLink>
        </div>

        <div v-else-if="enrichedForms.length === 0" class="text-center py-16 px-4 bg-white rounded-2xl border border-[#E6E8EE]">
          <i class="fa-solid fa-magnifying-glass text-4xl text-neutral-300 mx-auto mb-4"></i>
          <h3 class="text-lg font-bold text-neutral-900">No forms found</h3>
          <p class="mt-1 text-sm text-neutral-500">
            Your search and filter criteria did not match any forms.
          </p>
          <button
            class="btn-ghost border border-[#DEE1E7] px-4 py-2 rounded-xl mt-6 text-xs font-semibold text-[#565A62]"
            @click="clearFilters"
          >
            Clear Filters
          </button>
        </div>

        <!-- Grid View Mode -->
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
          <div
            v-for="form in paginatedForms"
            :key="form.id"
            class="group bg-white rounded-2xl border border-[#E6E8EE] p-5 hover:border-[#F4B5BD] hover:-translate-y-0.5 transition-all shadow-[inset_0_1px_0_0_#FFFFFF,0_1px_2px_rgba(23,25,35,0.04),0_12px_32px_-16px_rgba(23,25,35,0.14)] relative cursor-pointer"
            @click="navigateToSubmissions(form)"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="w-11 h-11 rounded-2xl flex items-center justify-center" :class="getFormIconBg(form)">
                <i :class="getFormIcon(form)" class="text-lg"></i>
              </div>
              <span :class="getStatusPillClass(form)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider">
                <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(form)"></span>
                {{ getStatusLabel(form) }}
              </span>
            </div>
            <p class="text-[14px] font-semibold text-[#1D1F24] truncate group-hover:text-[#EA6676] transition-colors">{{ form.title }}</p>
            <div class="flex items-center gap-1.5 mt-1">
              <p class="text-xs text-[#8E9198] font-medium truncate">{{ form.slug }}</p>
              <template v-if="form.tags?.length">
                <span class="text-[#C7C9CE] shrink-0">·</span>
                <span class="flex items-center gap-1 min-w-0">
                  <span
                    v-for="tag in form.tags.slice(0, 2)"
                    :key="tag"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#F0F1F4] text-[#6E7278] capitalize shrink-0"
                  >{{ tag }}</span>
                  <span v-if="form.tags.length > 2" class="text-[10px] text-[#A7ABB2] shrink-0">+{{ form.tags.length - 2 }}</span>
                </span>
              </template>
            </div>

            <div class="mt-4 pt-4 border-t border-[#ECEEF2] flex items-center justify-between">
              <div class="flex items-center gap-3 text-sm font-semibold text-[#565A62] tabular-nums">
                <span class="flex items-center gap-1.5">
                  <i class="fa-regular fa-eye text-[#A7ABB2] text-[13px]"></i>
                  {{ formatNumber(form.views_count) }}
                </span>
                <span class="flex items-center gap-1.5">
                  <i class="fa-regular fa-file-lines text-[#A7ABB2] text-[13px]"></i>
                  {{ formatNumber(form.submissions_count) }}
                </span>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity z-20 relative" @click.stop>
                <NuxtLink
                  :to="{ name: 'forms-slug-edit', params: { slug: form.slug } }"
                  class="w-7 h-7 flex items-center justify-center rounded-lg text-[#A7ABB2] hover:text-[#383B41] hover:bg-[#ECEEF2] transition-all"
                  title="Edit form"
                >
                  <i class="fa-solid fa-pen text-[11px]"></i>
                </NuxtLink>
                <button
                  @click="copyFormLink(form)"
                  class="w-7 h-7 flex items-center justify-center rounded-lg text-[#A7ABB2] hover:text-[#383B41] hover:bg-[#ECEEF2] transition-all"
                  title="Copy form link"
                >
                  <i class="fa-solid fa-link text-[11px]"></i>
                </button>
                <ExtraMenu :form="form" :is-main-page="true" portal="#home-portals">
                  <template #default="{ loading }">
                    <button
                      class="w-7 h-7 flex items-center justify-center rounded-lg text-[#A7ABB2] hover:text-[#383B41] hover:bg-[#ECEEF2] transition-all"
                      title="More actions"
                      :disabled="loading"
                    >
                      <i class="fa-solid fa-ellipsis text-[11px]"></i>
                    </button>
                  </template>
                </ExtraMenu>
              </div>
            </div>
          </div>

          <!-- New Form Dashed Card -->
          <NuxtLink
            v-if="!workspace?.is_readonly"
            :to="{ name: 'forms-create' }"
            class="rounded-2xl border-2 border-dashed border-[#DEE1E7] flex flex-col items-center justify-center gap-3 hover:border-[#EA6676]/40 hover:bg-[#FDE8EB]/40 transition-all min-h-[212px] group"
          >
            <span class="w-12 h-12 rounded-2xl bg-white border border-[#DEE1E7] flex items-center justify-center text-[#6E7278] group-hover:text-[#EA6676] group-hover:border-[#F4B5BD] transition-all shadow-[0_8px_20px_-12px_rgba(23,25,35,0.14)]">
              <i class="fa-solid fa-plus text-lg"></i>
            </span>
            <span class="text-sm font-semibold text-[#565A62] group-hover:text-[#1D1F24]">New form</span>
          </NuxtLink>
        </div>

        <!-- List View Mode -->
        <div v-else-if="viewMode === 'list'" class="mb-10 overflow-x-auto">
          <div class="hover-shadow bg-white rounded-2xl border border-[#E6E8EE] shadow-[inset_0_1px_0_0_#FFFFFF,0_1px_2px_rgba(23,25,35,0.04),0_16px_40px_-20px_rgba(23,25,35,0.18)] overflow-hidden min-w-[800px]">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-[#ECEEF2]">
                  <th class="px-8 py-5 text-[11px] font-medium uppercase tracking-[0.1em] text-[#8E9198]">Form Name</th>
                  <th class="px-6 py-5 text-[11px] font-medium uppercase tracking-[0.1em] text-[#8E9198]">Status</th>
                  <th class="px-6 py-5 text-[11px] font-medium uppercase tracking-[0.1em] text-[#8E9198]">Last Updated</th>
                  <th class="px-6 py-5 text-right text-[11px] font-medium uppercase tracking-[0.1em] text-[#8E9198]">Views</th>
                  <th class="px-6 py-5 text-right text-[11px] font-medium uppercase tracking-[0.1em] text-[#8E9198]">Responses</th>
                  <th class="px-6 py-5 text-right text-[11px] font-medium uppercase tracking-[0.1em] text-[#8E9198]">Completion</th>
                  <th class="px-8 py-5"></th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="form in paginatedForms"
                  :key="'list-' + form.id"
                  class="border-b border-[#ECEEF2] hover:bg-[#F7F8FA] transition-colors group relative cursor-pointer"
                  @click="navigateToSubmissions(form)"
                >
                  <td class="px-8 py-5">
                    <div class="flex items-center gap-4">
                      <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0" :class="getFormIconBg(form)">
                        <i :class="getFormIcon(form)" class="text-lg"></i>
                      </div>
                      <div>
                        <p class="text-[14px] font-semibold text-[#1D1F24] group-hover:text-[#EA6676] transition-colors">{{ form.title }}</p>
                        <p class="text-xs text-[#8E9198] font-medium mt-0.5">{{ form.slug }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-5">
                    <span :class="getStatusPillClass(form)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider">
                      <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(form)"></span>
                      {{ getStatusLabel(form) }}
                    </span>
                  </td>
                  <td class="px-6 py-5 text-sm text-[#6E7278] font-medium">{{ form.last_edited_human }}</td>
                  <td class="px-6 py-5 text-right">
                    <div class="flex items-center justify-end gap-2 text-sm font-semibold text-[#565A62] tabular-nums">
                      <i class="fa-regular fa-eye text-[#A7ABB2] text-[13px]"></i>
                      {{ formatNumber(form.views_count) }}
                    </div>
                  </td>
                  <td class="px-6 py-5 text-right">
                    <div class="flex items-center justify-end gap-2 text-sm font-semibold text-[#565A62] tabular-nums">
                      <i class="fa-regular fa-file-lines text-[#A7ABB2] text-[13px]"></i>
                      {{ formatNumber(form.submissions_count) }}
                    </div>
                  </td>
                  <td class="px-6 py-5">
                    <div class="flex items-center justify-end gap-2.5">
                      <div class="w-14 h-1.5 rounded-full bg-[#ECEEF2] overflow-hidden">
                        <div class="h-full rounded-full" :class="getCompletionBarColor(form)" :style="{ width: getCompletionPercentage(form) + '%' }"></div>
                      </div>
                      <span class="text-xs font-semibold text-[#565A62] tabular-nums">{{ getCompletionPercentage(form) }}%</span>
                    </div>
                  </td>
                  <td class="px-8 py-5 text-right" @click.stop>
                    <ExtraMenu :form="form" :is-main-page="true" portal="#home-portals">
                      <template #default="{ loading }">
                        <button
                          class="w-8 h-8 flex items-center justify-center rounded-lg text-[#C7C9CE] hover:text-[#383B41] hover:bg-[#ECEEF2] transition-all opacity-0 group-hover:opacity-100"
                          :disabled="loading"
                        >
                          <i class="fa-solid fa-ellipsis text-sm"></i>
                        </button>
                      </template>
                    </ExtraMenu>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Custom Client Side Pagination Controls -->
        <div v-if="totalPages > 1 && !isFormsLoading" class="flex items-center justify-between px-8 py-5 border-t border-[#ECEEF2] bg-[#F0F1F4] rounded-2xl mt-6">
          <p class="text-xs font-medium text-[#8E9198]">
            Showing <span class="text-[#565A62] tabular-nums font-semibold">{{ enrichedForms.length }}</span> of <span class="text-[#565A62] tabular-nums font-semibold">{{ forms?.length || 0 }}</span> forms
          </p>

          <div class="flex items-center gap-1.5">
            <button
              :disabled="currentPage === 1"
              @click="prevPage"
              class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#DEE1E7] text-[#C7C9CE] text-xs transition-colors enabled:hover:bg-[#F7F8FA] disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i class="fa-solid fa-chevron-left text-[9px]"></i>
            </button>
            <button class="btn-primary w-8 h-8 flex items-center justify-center rounded-lg text-white font-bold text-xs">
              {{ currentPage }}
            </button>
            <button
              :disabled="currentPage === totalPages"
              @click="nextPage"
              class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#DEE1E7] text-[#C7C9CE] text-xs transition-colors enabled:hover:bg-[#F7F8FA] disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i class="fa-solid fa-chevron-right text-[9px]"></i>
            </button>
          </div>
        </div>
      </ClientOnly>
    </div>

    <!-- Modals Portal Container -->
    <div id="home-portals" class="z-20" />
    <YearlyUpgradeModal />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useFuse } from "@vueuse/integrations/useFuse"
import { useLocalStorage, refDebounced, useClipboard } from "@vueuse/core"
import ExtraMenu from "~/components/pages/forms/show/ExtraMenu.vue"
import FormCardSkeleton from "~/components/pages/home/FormCardSkeleton.vue"
import { formatNumber, formatNumberWithCommas } from "~/lib/utils.js"

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
})

useOpnSeoMeta({
  title: "Your Forms",
  description:
    "All of your SharaForms are here. Create new forms, or update your existing forms.",
})

const route = useRoute()
const router = useRouter()

const activeTab = computed(() => {
  return route.query.tab || 'dashboard'
})

const { current: workspace, currentId: workspaceId } = useCurrentWorkspace()

const {
  forms,
  isLoading: isFormsLoading,
  isFetched,
} = useFormsList(workspaceId, {
  fetchAll: true,
  enabled: computed(() => import.meta.client && !!workspaceId.value),
})

// Dashboard stats
const selectedDays = ref(7)
const { dashboard } = useDashboardStats(workspaceId, { days: selectedDays })

// State
const search = ref("")
const debouncedSearch = refDebounced(search, 500)
const selectedTags = ref([])
const viewMode = useLocalStorage('sharaforms-viewmode', 'grid')
const currentPage = ref(1)
const itemsPerPage = 8
const showDateDropdown = ref(false)
const showFilterDropdown = ref(false)
const showSortDropdown = ref(false)
const statusFilter = ref('all')
const sortBy = ref('last_updated')
const sortDirection = ref('desc')
const filterDropdownRef = ref(null)
const sortDropdownRef = ref(null)

const dateOptions = [
  { label: 'Last 7 days', value: 7 },
  { label: 'Last 30 days', value: 30 },
  { label: 'Last 90 days', value: 90 },
]

const selectedDateLabel = computed(() => {
  return dateOptions.find(d => d.value === selectedDays.value)?.label || 'Last 7 days'
})

const selectDateRange = (days) => {
  selectedDays.value = days
  showDateDropdown.value = false
}

// Close dropdown on outside click
const dateDropdownRef = ref(null)
if (import.meta.client) {
  const closeDropdown = (e) => {
    if (showDateDropdown.value && dateDropdownRef.value && !dateDropdownRef.value.contains(e.target)) {
      showDateDropdown.value = false
    }
    if (showFilterDropdown.value && filterDropdownRef.value && !filterDropdownRef.value.contains(e.target)) {
      showFilterDropdown.value = false
    }
    if (showSortDropdown.value && sortDropdownRef.value && !sortDropdownRef.value.contains(e.target)) {
      showSortDropdown.value = false
    }
  }
  onMounted(() => document.addEventListener('click', closeDropdown))
  onUnmounted(() => document.removeEventListener('click', closeDropdown))
}

// Filter & Sort options
const statusFilterOptions = [
  { label: 'All', value: 'all' },
  { label: 'Live', value: 'live' },
  { label: 'Draft', value: 'draft' },
  { label: 'Closed', value: 'closed' },
]

const sortOptions = [
  { label: 'Last Updated', value: 'last_updated' },
  { label: 'Name', value: 'name' },
  { label: 'Views', value: 'views' },
  { label: 'Responses', value: 'responses' },
]

const selectStatusFilter = (value) => {
  statusFilter.value = value
  showFilterDropdown.value = false
}

const selectSort = (value) => {
  if (sortBy.value === value) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = value
    sortDirection.value = value === 'name' ? 'asc' : 'desc'
  }
  showSortDropdown.value = false
}

// Methods
const clearFilters = () => {
  search.value = ""
  selectedTags.value = []
  statusFilter.value = 'all'
}

// Computed
const isFilteringForms = computed(() => {
  return (
    (search.value !== "" && search.value !== null) ||
    selectedTags.value.length > 0 ||
    statusFilter.value !== 'all'
  )
})

const headerSubtitle = computed(() => {
  if (isFetched.value && !isFormsLoading.value) {
    if (forms.value?.length > 0) {
      const count = forms.value.length
      return `${count} ${count === 1 ? "form" : "forms"} in this workspace`
    }
    return "Create your first form to get started"
  }
  return "Manage and track your forms"
})

// Extract unique tags
const allTags = computed(() => {
  if (!forms.value) return []

  const tagsSet = new Set()
  forms.value.forEach((form) => {
    if (form.tags && form.tags.length) {
      form.tags.forEach((tag) => tagsSet.add(tag))
    }
  })

  return Array.from(tagsSet).sort()
})

const tagOptions = computed(() =>
  allTags.value.map((tag) => ({ label: tag, value: tag })),
)

const baseForms = computed(() => {
  if (!forms.value) return []
  return forms.value.filter((form) => {
    // Status filter
    if (statusFilter.value !== 'all') {
      const formStatus = getStatusLabel(form).toLowerCase()
      if (statusFilter.value !== formStatus) return false
    }
    // Tag filter
    if (selectedTags.value.length === 0) return true
    const selectedTagStrings = selectedTags.value
      .map((t) => (typeof t === "string" ? t : t?.value))
      .filter(Boolean)
    return form.tags && form.tags.length
      ? selectedTagStrings.every((tag) => form.tags.includes(tag))
      : false
  })
})

const { results: fuseResults } = useFuse(debouncedSearch, baseForms, {
  fuseOptions: {
    keys: ["title", "slug", "tags"],
    threshold: 0.3,
    ignoreLocation: true,
    includeScore: false,
  },
  matchAllWhenSearchEmpty: true,
})

const enrichedForms = computed(() => {
  const base = baseForms.value
  if (!base || base.length === 0) return []
  const results = fuseResults.value
  let items = results && results.length > 0 ? results.map((r) => r.item) : [...base]

  // Apply sorting
  items.sort((a, b) => {
    let cmp = 0
    switch (sortBy.value) {
      case 'name':
        cmp = (a.title || '').localeCompare(b.title || '')
        break
      case 'views':
        cmp = (a.views_count || 0) - (b.views_count || 0)
        break
      case 'responses':
        cmp = (a.submissions_count || 0) - (b.submissions_count || 0)
        break
      case 'last_updated':
      default:
        cmp = new Date(a.updated_at || 0) - new Date(b.updated_at || 0)
        break
    }
    return sortDirection.value === 'asc' ? cmp : -cmp
  })

  return items
})

// Pagination
const paginatedForms = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return enrichedForms.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(enrichedForms.value.length / itemsPerPage) || 1)

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++
}

watch(enrichedForms, () => {
  currentPage.value = 1
})

// KPI Computations — use dashboard API for period-specific data, forms list for all-time
const totalViews = computed(() => {
  if (dashboard.value) return dashboard.value.views.current
  if (!forms.value) return 0
  return forms.value.reduce((sum, f) => sum + (f.views_count || 0), 0)
})

const totalResponses = computed(() => {
  if (dashboard.value) return dashboard.value.submissions.current
  if (!forms.value) return 0
  return forms.value.reduce((sum, f) => sum + (f.submissions_count || 0), 0)
})

const conversionRate = computed(() => {
  if (dashboard.value) return dashboard.value.conversion_rate.current
  if (!totalViews.value) return 0
  return (totalResponses.value / totalViews.value) * 100
})

// Trend data from dashboard API
const viewsTrend = computed(() => {
  return dashboard.value?.views?.trend || { percentage: 0, direction: 'neutral' }
})

const submissionsTrend = computed(() => {
  return dashboard.value?.submissions?.trend || { percentage: 0, direction: 'neutral' }
})

const conversionTrend = computed(() => {
  return dashboard.value?.conversion_rate?.trend || { percentage: 0, direction: 'neutral' }
})

const previousViewsTotal = computed(() => {
  return dashboard.value?.views?.previous || 0
})

const liveFormsCount = computed(() => {
  if (!forms.value) return 0
  return forms.value.filter(form => {
    const isClosed = form.visibility === "closed" || form.is_closed || form.max_number_of_submissions_reached
    return form.visibility === 'public' && !isClosed
  }).length
})

const liveFormsPercentage = computed(() => {
  if (!forms.value?.length) return 0
  return (liveFormsCount.value / forms.value.length) * 100
})

// Weekly Chart Computations — use real daily data from dashboard API
const weeklyData = computed(() => {
  const chartData = dashboard.value?.chart
  if (chartData && chartData.length > 0) {
    const peak = Math.max(...chartData.map(d => d.views), 1)
    return chartData.map(d => ({
      label: d.label,
      count: d.views,
      height: (d.views / peak) * 100,
      peak: d.views === peak,
    }))
  }

  // Fallback: adapt label count to selected period
  const count = Math.min(selectedDays.value, 14) // cap at 14 bars to avoid overflow
  return Array.from({ length: count }, () => ({
    label: '',
    count: 0,
    height: 0,
    peak: false,
  }))
})

const peakChartValue = computed(() => {
  const counts = weeklyData.value.map(d => d.count)
  return Math.max(...counts) || 250
})

const peakDayText = computed(() => {
  const maxDay = [...weeklyData.value].sort((a, b) => b.count - a.count)[0]
  return `${maxDay.label}, ${formatNumberWithCommas(maxDay.count)} views`
})

const dailyAverage = computed(() => {
  const days = weeklyData.value.map(d => d.count)
  const sum = days.reduce((a, b) => a + b, 0)
  return days.length > 0 ? Math.round(sum / days.length) : 0
})

// Top Performing Forms
const topPerformingForms = computed(() => {
  if (!forms.value) return []
  return [...forms.value]
    .sort((a, b) => (b.submissions_count || 0) - (a.submissions_count || 0))
    .slice(0, 4)
})

const getProgressWidth = (form) => {
  if (!totalResponses.value) return 0
  return Math.round((form.submissions_count / totalResponses.value) * 100)
}

// Visual Helpers
const getFormIcon = (form) => {
  if (form.visibility === "draft") return "fa-solid fa-square-pen"
  const isClosed = form.visibility === "closed" || form.is_closed || form.max_number_of_submissions_reached
  if (isClosed) return "fa-solid fa-lock"
  
  const title = (form.title || '').toLowerCase()
  if (title.includes('feedback') || title.includes('survey')) return "fa-solid fa-face-smile"
  if (title.includes('waitlist') || title.includes('signup') || title.includes('register')) return "fa-solid fa-user"
  return "fa-solid fa-file-lines"
}

const getFormIconBg = (form) => {
  if (form.visibility === "draft") return "bg-[#FFF3D6] text-[#d97706]"
  const isClosed = form.visibility === "closed" || form.is_closed || form.max_number_of_submissions_reached
  if (isClosed) return "bg-[#fce7e2] text-[#c2351f]"
  
  const title = (form.title || '').toLowerCase()
  if (title.includes('feedback') || title.includes('survey')) return "bg-[#EEF0FD] text-[#6366f1]"
  if (title.includes('waitlist') || title.includes('signup') || title.includes('register')) return "bg-[#EFF8F1] text-[#16a34a]"
  return "bg-[#E4F4F8] text-[#0891b2]"
}

const getStatusLabel = (form) => {
  if (form.visibility === 'draft') return 'Draft'
  const isClosed = form.visibility === "closed" || form.is_closed || form.max_number_of_submissions_reached
  if (isClosed) return 'Closed'
  return 'Live'
}

const getStatusPillClass = (form) => {
  if (form.visibility === 'draft') return 'pill-draft'
  const isClosed = form.visibility === "closed" || form.is_closed || form.max_number_of_submissions_reached
  if (isClosed) return 'pill-closed'
  return 'pill-live'
}

const getStatusDotClass = (form) => {
  if (form.visibility === 'draft') return 'bg-[#d97706]'
  const isClosed = form.visibility === "closed" || form.is_closed || form.max_number_of_submissions_reached
  if (isClosed) return 'bg-[#c2351f]'
  return 'bg-[#16a34a]'
}

const getCompletionPercentage = (form) => {
  if (!form.views_count) return 0
  return Math.round(((form.submissions_count || 0) / form.views_count) * 100)
}

const getCompletionBarColor = (form) => {
  if (form.visibility === 'draft') return 'bg-[#d97706]'
  const isClosed = form.visibility === "closed" || form.is_closed || form.max_number_of_submissions_reached
  if (isClosed) return 'bg-[#c2351f]'
  return 'bg-[#0891b2]'
}

const { copy } = useClipboard()
const copyFormLink = (form) => {
  if (form.visibility === 'draft') {
    useAlert().warning("This form is currently in Draft mode and is not publicly accessible.")
    return
  }
  copy(form.share_url)
  useAlert().success("Form link copied!")
}

const navigateToSubmissions = (form) => {
  router.push({ name: 'forms-slug-show-submissions', params: { slug: form.slug } })
}
</script>
