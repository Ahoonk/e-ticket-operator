<template>
  <section class="flex h-full min-h-0 flex-col gap-4 lg:grid lg:grid-cols-[260px_1fr] lg:gap-6">
    <aside class="hidden min-h-0 flex-col rounded-2xl border border-slate-800 bg-slate-900/70 p-3 sm:p-4 lg:flex lg:h-full">
      <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/20 text-lg font-semibold text-emerald-200">
          {{ initials }}
        </div>
        <div>
          <p class="text-sm font-semibold text-white">{{ user?.name || 'User' }}</p>
          <p class="text-xs text-slate-400">{{ roleLabel }}</p>
        </div>
      </div>

      <nav class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-2 lg:flex lg:flex-col">
        <button
          v-for="item in visibleMenu"
          :key="item.key"
          class="flex items-center justify-between rounded-xl px-3 py-2 text-left text-xs font-medium transition sm:text-sm"
          :class="
            active === item.key
              ? 'bg-emerald-500/15 text-emerald-200 border border-emerald-400/40'
              : 'border border-transparent text-slate-300 hover:border-slate-700'
          "
          @click="active = item.key"
        >
          {{ item.label }}
          <span class="text-xs text-slate-500">></span>
        </button>
      </nav>

      <div class="mt-auto pt-4">
        <button
          class="w-full rounded-xl border border-rose-500/40 px-3 py-2 text-sm font-semibold text-rose-200 hover:border-rose-400"
          @click="emit('logout')"
        >
          Logout
        </button>
      </div>
    </aside>

    <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-3 lg:hidden">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-base font-semibold text-emerald-200">
            {{ initials }}
          </div>
          <div class="sr-only">
            <p>{{ user?.name || 'User' }}</p>
            <p>{{ roleLabel }}</p>
          </div>
        </div>
        <button
          type="button"
          class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-700 text-slate-200 hover:border-slate-500"
          :aria-label="showMobileMenu ? 'Tutup menu' : 'Buka menu'"
          :title="showMobileMenu ? 'Tutup menu' : 'Buka menu'"
          @click="showMobileMenu = !showMobileMenu"
        >
          <svg v-if="!showMobileMenu" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M3 5h14v2H3V5zm0 4h14v2H3V9zm0 4h14v2H3v-2z"></path>
          </svg>
          <svg v-else class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M5.3 4.3 10 9l4.7-4.7 1.4 1.4L11.4 10l4.7 4.7-1.4 1.4L10 11.4l-4.7 4.7-1.4-1.4L8.6 10 3.9 5.3l1.4-1.4z"></path>
          </svg>
        </button>
      </div>

      <div v-if="showMobileMenu" class="mt-3 border-t border-slate-800 pt-3">
        <nav class="grid grid-cols-3 gap-2">
          <button
            v-for="item in visibleMenu"
            :key="item.key"
            class="flex flex-col items-center justify-center gap-2 rounded-xl px-2 py-3 text-xs font-medium transition"
            :class="
              active === item.key
                ? 'bg-emerald-500/15 text-emerald-200 border border-emerald-400/40'
                : 'border border-transparent text-slate-300 hover:border-slate-700'
            "
            @click="active = item.key; showMobileMenu = false"
            :aria-label="item.label"
            :title="item.label"
          >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path :d="menuIconPath(item.key)"></path>
            </svg>
          </button>
        </nav>

        <div class="mt-3">
          <button
            class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-rose-500/40 text-rose-200 hover:border-rose-400"
            aria-label="Logout"
            title="Logout"
            @click="emit('logout')"
          >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path d="M7 3a2 2 0 0 0-2 2v2h2V5h8v10H7v-2H5v2a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7zm-1.6 6.4 2.3-2.3L9.1 8.5 8.2 9.4H15v1.2H8.2l.9.9-1.4 1.4-2.3-2.5z"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div class="min-w-0 flex h-full min-h-0 flex-col rounded-2xl border border-slate-800 bg-slate-900/60 p-4 sm:p-6">
      <div class="min-h-0 flex-1 overflow-hidden pr-1">
        <KegiatanPanel
          v-if="active === 'kegiatan'"
          :refresh-key="refreshKey"
          :can-manage="canManageKegiatan"
          :can-complete="props.user?.role === 'user'"
          :can-upload="props.user?.role === 'user'"
          :current-user="props.user"
          @create="openCreate"
          @edit="openEdit"
        />

        <div v-else class="h-full min-h-0 overflow-y-auto pr-1">
          <KegiatanForm
            v-if="active === 'kegiatan-form'"
            :initial-data="editingItem"
            @saved="handleSaved"
            @cancel="handleCancel"
          />
          <AnggotaPanel v-else-if="active === 'anggota'" />
          <OpdPanel v-else-if="active === 'opd'" />
          <UserPanel v-else-if="active === 'user'" :current-user="props.user" />
          <DokumenPanel
            v-else-if="active === 'dokumen'"
            :can-delete="props.user?.role === 'admin' || props.user?.role === 'superadmin'"
          />
          <PlaceholderPanel v-else />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watchEffect } from 'vue';
import AnggotaPanel from '../components/AnggotaPanel.vue';
import KegiatanForm from '../components/KegiatanForm.vue';
import KegiatanPanel from '../components/KegiatanPanel.vue';
import DokumenPanel from '../components/DokumenPanel.vue';
import OpdPanel from '../components/OpdPanel.vue';
import PlaceholderPanel from '../components/PlaceholderPanel.vue';
import UserPanel from '../components/UserPanel.vue';

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['logout']);

const menu = [
  { key: 'kegiatan', label: 'Daftar Kegiatan' },
  { key: 'anggota', label: 'Daftar Anggota' },
  { key: 'opd', label: 'Daftar OPD' },
  { key: 'user', label: 'Daftar User' },
  { key: 'dokumen', label: 'Dokumentasi' },
];

const active = ref('kegiatan');
const refreshKey = ref(0);
const editingItem = ref(null);
const showMobileMenu = ref(false);

const canManageKegiatan = computed(() => props.user?.role !== 'user');

const visibleMenu = computed(() => {
  if (props.user?.role === 'superadmin') {
    return menu;
  }

  if (props.user?.role === 'admin') {
    return menu;
  }

  if (props.user?.role === 'user') {
    return menu.filter((item) => item.key === 'kegiatan');
  }

  return menu.filter((item) => ['kegiatan', 'opd'].includes(item.key));
});

const activeLabel = computed(() => {
  if (active.value === 'kegiatan-form') return 'Buat Kegiatan';
  return visibleMenu.value.find((item) => item.key === active.value)?.label || 'Menu';
});

const roleLabel = computed(() => {
  if (props.user?.role === 'superadmin') return 'Superadmin';
  if (props.user?.role === 'admin') return 'Admin';
  if (props.user?.role === 'user') return 'User';
  return 'User';
});

const menuIconPath = (key) => {
  if (key === 'kegiatan') {
    return 'M4 5h12v2H4V5zm0 4h12v2H4V9zm0 4h8v2H4v-2z';
  }
  if (key === 'anggota') {
    return 'M10 10.5a3.5 3.5 0 1 0-3.5-3.5 3.5 3.5 0 0 0 3.5 3.5zm0 1.5c-3.3 0-6 1.8-6 4v1h12v-1c0-2.2-2.7-4-6-4z';
  }
  if (key === 'opd') {
    return 'M4 17V4h12v13H4zm2-2h8V6H6v9zm1-6h2V8H7v1zm0 3h2v-1H7v1zm4-3h2V8h-2v1zm0 3h2v-1h-2v1z';
  }
  if (key === 'user') {
    return 'M10 3a3 3 0 1 1 0 6 3 3 0 0 1 0-6zm0 7c-3 0-5.5 1.8-5.5 4v3h11v-3c0-2.2-2.5-4-5.5-4z';
  }
  if (key === 'dokumen') {
    return 'M6 2h6l4 4v12H6V2zm6 1.5V6h2.5L12 3.5zM8 9h4v1.5H8V9zm0 3h4v1.5H8V12z';
  }
  return 'M4 5h12v10H4V5z';
};

const initials = computed(() => {
  const name = props.user?.name || '';
  const parts = name.trim().split(' ').filter(Boolean);
  if (parts.length === 0) return 'U';
  if (parts.length === 1) return parts[0][0]?.toUpperCase() || 'U';
  return (parts[0][0] + parts[1][0]).toUpperCase();
});

const allowedKeys = computed(() => [
  ...visibleMenu.value.map((item) => item.key),
  'kegiatan-form',
]);

watchEffect(() => {
  if (!allowedKeys.value.includes(active.value)) {
    active.value = visibleMenu.value[0]?.key || 'kegiatan';
  }
});

const openCreate = () => {
  editingItem.value = null;
  active.value = 'kegiatan-form';
};

const openEdit = (item) => {
  editingItem.value = item;
  active.value = 'kegiatan-form';
};

const handleSaved = () => {
  active.value = 'kegiatan';
  refreshKey.value += 1;
};

const handleCancel = () => {
  active.value = 'kegiatan';
};
</script>
