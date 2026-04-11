<template>
  <div class="fixed inset-0 overflow-hidden bg-slate-950 text-slate-100 font-['Space Grotesk']">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
      <div class="absolute -top-40 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-emerald-500/20 blur-3xl"></div>
      <div class="absolute bottom-0 right-0 h-72 w-72 translate-x-1/3 translate-y-1/3 rounded-full bg-sky-500/20 blur-3xl"></div>
    </div>

    <main
      class="relative mx-auto flex h-full w-full max-w-[1400px] flex-col gap-6 px-4 py-8 sm:gap-8 sm:px-6 sm:py-10 lg:gap-10 lg:px-8 lg:py-12"
      :class="!user ? 'overflow-hidden' : 'overflow-hidden'"
    >
      <header>
        <p class="text-sm uppercase tracking-[0.3em] text-emerald-300/70">Kegiatan Jaringan : LIVE REPORT</p>
        <h1 class="mt-2 text-2xl font-semibold text-white sm:text-3xl lg:text-4xl">Kegiatan Tim TA Operator Komputer</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300 sm:text-base">
          Pantau Pekerjaan Tim Secara Real Time
        </p>
      </header>

      <div v-if="loading" class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
        <p class="text-slate-300">Memuat data user...</p>
      </div>

      <div v-else-if="!user" class="relative flex min-h-0 flex-1 flex-col overflow-hidden">
        <PublicKegiatanCards>
          <template #action>
            <button
              class="rounded-full border border-slate-700 px-4 py-2 text-sm font-medium text-slate-200 hover:border-slate-500"
              @click="showLogin = true"
            >
              Masuk
            </button>
          </template>
        </PublicKegiatanCards>

        <div
          v-if="showLogin"
          class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4"
        >
          <div class="relative w-full max-w-md">
            <button
              class="absolute right-3 top-3 rounded-full border border-slate-700 px-2 py-1 text-xs text-slate-200 hover:border-slate-500"
              @click="showLogin = false"
            >
              Tutup
            </button>
            <AuthPanel :error="error" @login="loginUser" />
          </div>
        </div>
      </div>

      <Dashboard v-else class="min-h-0 flex-1" :user="user" @logout="logoutUser" />
    </main>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useAuth } from './composables/useAuth';
import AuthPanel from './components/AuthPanel.vue';
import PublicKegiatanCards from './components/PublicKegiatanCards.vue';
import Dashboard from './pages/Dashboard.vue';

const { user, loading, error, init, loginUser, logoutUser } = useAuth();
const showLogin = ref(false);

onMounted(() => {
  init();
});
</script>
