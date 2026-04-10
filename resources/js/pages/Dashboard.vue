<template>
  <section class="grid gap-6 lg:grid-cols-[260px_1fr]">
    <aside class="flex min-h-0 flex-col rounded-2xl border border-slate-800 bg-slate-900/70 p-3 sm:p-4 lg:min-h-[520px]">
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

    <div class="min-w-0 rounded-2xl border border-slate-800 bg-slate-900/60 p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
        <div>
          <h2 class="text-xl font-semibold text-white">{{ activeLabel }}</h2>
          <p class="text-sm text-slate-400">Kelola data pada menu ini.</p>
        </div>
      </div>

      <div class="mt-6">
        <KegiatanPanel
          v-if="active === 'kegiatan'"
          :refresh-key="refreshKey"
          @create="openCreate"
          @edit="openEdit"
        />
        <KegiatanForm
          v-else-if="active === 'kegiatan-form'"
          :initial-data="editingItem"
          @saved="handleSaved"
          @cancel="handleCancel"
        />
        <AnggotaPanel v-else-if="active === 'anggota'" />
        <OpdPanel v-else-if="active === 'opd'" />
        <UserPanel v-else-if="active === 'user' && isSuperadmin" />
        <PlaceholderPanel v-else />
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watchEffect } from 'vue';
import AnggotaPanel from '../components/AnggotaPanel.vue';
import KegiatanForm from '../components/KegiatanForm.vue';
import KegiatanPanel from '../components/KegiatanPanel.vue';
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
];

const active = ref('kegiatan');
const refreshKey = ref(0);
const editingItem = ref(null);

const isSuperadmin = computed(() => props.user?.role === 'superadmin');

const visibleMenu = computed(() => {
  if (props.user?.role === 'superadmin') {
    return menu;
  }

  if (props.user?.role === 'admin') {
    return menu.filter((item) => item.key !== 'user');
  }

  if (props.user?.role === 'user') {
    return menu.filter((item) => ['kegiatan', 'opd'].includes(item.key));
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
