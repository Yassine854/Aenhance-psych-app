<template>
  <!-- Language Bar -->
  <div class="flex justify-between items-center px-3 py-2 bg-[#af5166] text-white shadow-md text-[12px] relative">
    <div class="relative">
      <button @click="showDropdown = !showDropdown" class="flex items-center gap-1.5 text-[13px]">
        🌐 {{ currentLang }}
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
      <div v-if="showDropdown" class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-max z-50">
        <button v-for="lang in languages" :key="lang.code" @click="setLang(lang.code)"
          class="block w-full text-left px-3 py-1 hover:bg-gray-200 transition text-[13px]">
          {{ lang.label }}
        </button>
      </div>
    </div>

    <div class="flex gap-2.5 items-center">
      <!-- Patient notifications (navbar) -->
      <div v-if="isPatient" class="relative" ref="patientNotificationDropdownRef">
        <button
          type="button"
          @click.stop="togglePatientNotifications"
          class="inline-flex items-center justify-center p-2 bg-white/10 text-white rounded-full border border-white/20 hover:bg-white/20 transition"
          :aria-label="t('navbar.notifications')"
          :title="t('navbar.notifications')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a3 3 0 006 0" />
          </svg>
        </button>
        <span
          v-if="patientUnreadCount > 0"
          class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold leading-none text-white bg-red-500 rounded-full shadow-md"
          role="status"
          aria-live="polite"
          aria-atomic="true"
        >
          {{ patientUnreadCount }}
        </span>

        <div
          v-if="showPatientNotifications"
          @click.stop
          :class="['absolute top-full mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-80 z-50 overflow-hidden', isRtl ? 'left-0' : 'right-0']"
        >
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:bg-gray-700 flex items-center justify-between">
            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('navbar.notifications') }}</div>
            <button
              type="button"
              class="text-xs font-medium text-[#5997ac] disabled:opacity-50"
              @click="markAllPatientNotificationsAsRead"
              :disabled="patientUnreadCount === 0"
            >
              {{ t('navbar.markAllAsRead') }}
            </button>
          </div>

          <ul class="max-h-80 overflow-y-auto">
            <li
              v-for="notification in patientNotifications"
              :key="notification.id"
              class="border-b border-gray-100 dark:border-gray-700"
            >
              <button
                type="button"
                class="w-full text-left px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                :class="notification.is_read ? 'opacity-80' : ''"
                @click="openPatientNotification(notification)"
              >
                <div class="flex items-start gap-2">
                  <span
                    class="mt-1 w-2 h-2 rounded-full"
                    :class="notification.is_read ? 'bg-gray-300' : 'bg-green-500'"
                  ></span>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ notification.title }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ notification.message }}</div>
                    <div class="text-[11px] text-gray-400 mt-1">{{ notification.time_ago }}</div>
                  </div>
                </div>
              </button>
            </li>

            <li v-if="patientNotifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
              {{ t('navbar.noNotifications') }}
            </li>
          </ul>

          <button
            type="button"
            class="w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            @click="viewAllPatientNotifications"
          >
            {{ t('navbar.viewAll') }}
          </button>
        </div>
      </div>

      <!-- Appointments icon (shows pending appointments count for patient) -->
      <div v-if="isPatient" class="relative">
        <Link
          :href="route('patient.appointments')"
          class="inline-flex items-center justify-center p-2 bg-white/10 text-white rounded-full border border-white/20 hover:bg-white/20 transition mr-2"
          :aria-label="t('navbar.appointments')"
          :title="t('navbar.appointments')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="1.5" />
            <path d="M16 2v4M8 2v4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M3 10h18" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M9 14l2 2 4-4" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </Link>
        <span
          v-if="patientCartLocal > 0"
          class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold leading-none text-white bg-red-500 rounded-full shadow-md transform transition-all duration-150"
          :class="{ 'scale-110 ring-4 ring-red-300/40': _pulse }"
          role="status"
          aria-live="polite"
          aria-atomic="true"
        >
          {{ patientCartLocal }}
        </span>
      </div>

      <!-- Patient menu (shown when a patient is logged in) -->
      <div v-if="isPatient" class="relative">
        <button
          type="button"
          @click.stop="showPatientMenu = !showPatientMenu"
          class="flex items-center gap-2 px-3.5 py-1.5 bg-white/10 text-white text-[12px] rounded-full border border-white/20 hover:bg-white/20 transition"
        >
          <span class="inline-flex h-7 w-7 items-center justify-center rounded-full overflow-hidden ring-2 ring-white/25 bg-white/10">
            <img
              v-if="patientAvatarUrl"
              :src="patientAvatarUrl"
              alt="Profile"
              class="h-full w-full object-cover"
            />
            <span v-else class="text-[11px] font-semibold tracking-wide">{{ patientInitials }}</span>
          </span>
          <span class="max-w-[180px] truncate">{{ patientDisplayName }}</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <div
          v-if="showPatientMenu"
          ref="patientMenuRef"
          @click.stop
          :class="['absolute top-full mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-56 z-50 overflow-hidden', isRtl ? 'left-0' : 'right-0']"
        >
          <!-- Profile Header -->
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:bg-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-gray-200 dark:ring-gray-600">
                <img
                  v-if="patientAvatarUrl"
                  :src="patientAvatarUrl"
                  alt="Profile"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full bg-[#f6aec2] flex items-center justify-center text-white font-semibold">
                  {{ patientInitials }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ patientDisplayName }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ t('navbar.patient') }}
                </div>
              </div>
            </div>
          </div>

          <Link
            :href="route('patient.profile')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPatientMenu = false"
          >
            {{ t('navbar.editProfile') }}
          </Link>
          <Link
            :href="route('patient.account')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPatientMenu = false"
          >
            {{ t('navbar.telementalHealth') }}
          </Link>
          <Link
            :href="route('patient.appointments')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPatientMenu = false"
          >
            {{ t('navbar.appointments') }}
          </Link>
          <Link
            :href="route('patient.payments')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPatientMenu = false"
          >
            {{ t('navbar.paymentHistory') }}
          </Link>
          <div class="border-t border-gray-200"></div>
          <button
            type="button"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click.prevent="handlePatientLogout"
          >
            {{ t('navbar.logout') }}
          </button>
        </div>
      </div>

      <!-- Psychologist notifications (navbar) -->
      <div v-if="isPsychologist" class="relative" ref="psychologistNotificationDropdownRef">
        <button
          type="button"
          @click.stop="togglePsychologistNotifications"
          class="inline-flex items-center justify-center p-2 bg-white/10 text-white rounded-full border border-white/20 hover:bg-white/20 transition"
          :aria-label="t('navbar.notifications')"
          :title="t('navbar.notifications')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a3 3 0 006 0" />
          </svg>
        </button>
        <span
          v-if="psychologistUnreadCount > 0"
          class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold leading-none text-white bg-red-500 rounded-full shadow-md"
          role="status"
          aria-live="polite"
          aria-atomic="true"
        >
          {{ psychologistUnreadCount }}
        </span>

        <div
          v-if="showPsychologistNotifications"
          @click.stop
          :class="['absolute top-full mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-80 z-50 overflow-hidden', isRtl ? 'left-0' : 'right-0']"
        >
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:bg-gray-700 flex items-center justify-between">
            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('navbar.notifications') }}</div>
            <button
              type="button"
              class="text-xs font-medium text-[#5997ac] disabled:opacity-50"
              @click="markAllPsychologistNotificationsAsRead"
              :disabled="psychologistUnreadCount === 0"
            >
              {{ t('navbar.markAllAsRead') }}
            </button>
          </div>

          <ul class="max-h-80 overflow-y-auto">
            <li
              v-for="notification in psychologistNotifications"
              :key="notification.id"
              class="border-b border-gray-100 dark:border-gray-700"
            >
              <button
                type="button"
                class="w-full text-left px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                :class="notification.is_read ? 'opacity-80' : ''"
                @click="openPsychologistNotification(notification)"
              >
                <div class="flex items-start gap-2">
                  <span
                    class="mt-1 w-2 h-2 rounded-full"
                    :class="notification.is_read ? 'bg-gray-300' : 'bg-green-500'"
                  ></span>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ notification.title }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ notification.message }}</div>
                    <div class="text-[11px] text-gray-400 mt-1">{{ notification.time_ago }}</div>
                  </div>
                </div>
              </button>
            </li>

            <li v-if="psychologistNotifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
              {{ t('navbar.noNotifications') }}
            </li>
          </ul>

          <button
            type="button"
            class="w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            @click="viewAllPsychologistNotifications"
          >
            {{ t('navbar.viewAll') }}
          </button>
        </div>
      </div>

      <!-- Psychologist menu (shown when a psychologist is logged in) -->
      <div v-if="isPsychologist" class="relative">
        <button
          type="button"
          @click.stop="showPsychologistMenu = !showPsychologistMenu"
          class="flex items-center gap-2 px-3.5 py-1.5 bg-white/10 text-white text-[12px] rounded-full border border-white/20 hover:bg-white/20 transition"
        >
          <span class="inline-flex h-7 w-7 items-center justify-center rounded-full overflow-hidden ring-2 ring-white/25 bg-white/10">
            <img
              v-if="psychologistAvatarUrl"
              :src="psychologistAvatarUrl"
              alt="Profile"
              class="h-full w-full object-cover"
            />
            <span v-else class="text-[11px] font-semibold tracking-wide">{{ psychologistInitials }}</span>
          </span>
          <span class="max-w-[180px] truncate">{{ psychologistDisplayName }}</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <div
          v-if="showPsychologistMenu"
          ref="psychologistMenuRef"
          @click.stop
          :class="['absolute top-full mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-56 z-50 overflow-hidden', isRtl ? 'left-0' : 'right-0']"
        >
          <!-- Profile Header -->
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:bg-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-gray-200 dark:ring-gray-600">
                <img
                  v-if="psychologistAvatarUrl"
                  :src="psychologistAvatarUrl"
                  alt="Profile"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full bg-[#5997ac] flex items-center justify-center text-white font-semibold">
                  {{ psychologistInitials }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ psychologistDisplayName }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ t('navbar.psychologist') }}
                </div>
              </div>
            </div>
          </div>

          <Link
            :href="route('psychologist.profile.self')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            {{ t('navbar.editProfile') }}
          </Link>

          <Link
            :href="route('psychologist.account')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            {{ t('navbar.accountSettings') }}
          </Link>

          <Link
            :href="route('psychologist.availabilities')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            {{ t('navbar.manageAvailability') }}
          </Link>

          <Link
            :href="route('psychologist.appointments.index')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            {{ t('navbar.appointments') }}
          </Link>

          <Link
            :href="route('psychologist.patients.index')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            {{ t('navbar.patients') }}
          </Link>

          <Link
            :href="route('psychologist.payouts.index')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            {{ t('navbar.payouts') }}
          </Link>
          <div class="border-t border-gray-200"></div>
          <button
            type="button"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click.prevent="handlePsychologistLogout"
          >
            {{ t('navbar.logout') }}
          </button>
        </div>
      </div>

      <!-- Guest auth buttons -->
      <Link v-if="!activeAuthUser && canLogin" :href="route('login')"
        class="px-3.5 py-1.5 bg-[#5997ac] text-white text-[12px] rounded flex items-center gap-1.5 justify-center hover:bg-[#467891] transition">
        🔓 {{ t("login") }}
      </Link>
      <Link v-if="!activeAuthUser && canRegister" :href="route('register')"
        class="px-3.5 py-1.5 bg-[#f6aec2] text-white text-[12px] rounded flex items-center gap-1.5 justify-center hover:bg-[#e190b0] transition">
        👤 {{ t("register") }}
      </Link>
    </div>
  </div>

  <!-- Navigation Bar -->
  <nav class="bg-white shadow-sm">
    <div class="mx-auto px-4">
      <div class="flex items-center justify-between py-3">
        <!-- Logo (Extreme Left) -->
        <div class="flex-shrink-0">
          <Link :href="route('home')" class="cursor-pointer">
            <img src="/storage/aenhance.svg" alt="Logo" class="h-14 w-auto object-contain hover:opacity-80 transition-opacity" />
          </Link>
        </div>

        <!-- Desktop Navigation -->
        <ul class="hidden lg:flex gap-8 items-center flex-1 justify-center">
          <!-- About -->
          <li class="relative">
            <button @click="showAboutDropdown = !showAboutDropdown"
              class="px-3 py-1 text-gray-700 font-medium flex items-center gap-1 relative group hover:bg-gray-100 transition">
              {{ t('nav.about') }}
              <svg class="w-3 h-3 mt-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 111.02 1.1l-4 3.625a.75.75 0 01-1.02 0l-4-3.625a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </button>
            <ul v-if="showAboutDropdown" class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-52 z-50">
              <li v-for="item in aboutItems" :key="item">
                <Link 
                  :href="item.href" 
                  class="block px-4 py-2 hover:bg-gray-100 text-sm transition"
                  @click="showAboutDropdown = false"
                >
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </li>

          <!-- Other links -->
          <li>
            <Link
              :href="route('services.index')"
              class="px-3 py-1 text-gray-700 font-medium hover:bg-gray-100 transition"
            >
              {{ t('nav.services') }}
            </Link>
          </li>
          
          <!-- Support Dropdown -->
          <li class="relative">
            <button @click="showSupportDropdown = !showSupportDropdown"
              class="px-3 py-1 text-gray-700 font-medium flex items-center gap-1 relative group hover:bg-gray-100 transition">
              {{ t('nav.support') }}
              <svg class="w-3 h-3 mt-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 111.02 1.1l-4 3.625a.75.75 0 01-1.02 0l-4-3.625a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </button>
            <ul v-if="showSupportDropdown" class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-52 z-50">
              <li v-for="item in supportItems" :key="item.label">
                <Link 
                  :href="item.href" 
                  class="block px-4 py-2 hover:bg-gray-100 text-sm transition"
                  @click="showSupportDropdown = false"
                >
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </li>
          
          <li>
            <Link
              :href="route('ressources.index')"
              class="px-3 py-1 text-gray-700 font-medium hover:bg-gray-100 transition"
            >
              {{ t('nav.resources') }}
            </Link>
          </li>
          <li>
            <Link
              :href="route('blogs.index')"
              class="px-3 py-1 text-gray-700 font-medium hover:bg-gray-100 transition"
            >
              {{ t('nav.blog') }}
            </Link>
          </li>
        </ul>

        <!-- Hamburger (mobile) -->
        <button @click="showMobileMenu = !showMobileMenu" class="lg:hidden p-2 rounded-md border border-gray-300">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="showMobileMenu" class="lg:hidden border-t border-gray-200 py-3">
        <ul class="space-y-2">
          <!-- About -->
          <li>
            <button @click="showAboutDropdown = !showAboutDropdown"
              class="w-full text-left px-3 py-2 text-gray-700 font-medium flex items-center justify-between hover:bg-gray-100 transition">
              {{ t('nav.about') }}
              <svg class="w-4 h-4" :class="{ 'rotate-180': showAboutDropdown }" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 111.02 1.1l-4 3.625a.75.75 0 01-1.02 0l-4-3.625a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </button>
            <ul v-if="showAboutDropdown" class="pl-6 mt-2 space-y-1">
              <li v-for="item in aboutItems" :key="item">
                <Link 
                  :href="item.href" 
                  class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 transition"
                  @click="showAboutDropdown = false; showMobileMenu = false"
                >
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </li>

          <!-- Other links -->
          <li>
            <Link
              :href="route('services.index')"
              class="block px-3 py-2 text-gray-700 font-medium hover:bg-gray-100 transition"
              @click="showMobileMenu = false"
            >
              {{ t('nav.services') }}
            </Link>
          </li>
          
          <!-- Support Dropdown (Mobile) -->
          <li>
            <button @click="showSupportDropdown = !showSupportDropdown"
              class="w-full text-left px-3 py-2 text-gray-700 font-medium flex items-center justify-between hover:bg-gray-100 transition">
              {{ t('nav.support') }}
              <svg class="w-4 h-4" :class="{ 'rotate-180': showSupportDropdown }" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 111.02 1.1l-4 3.625a.75.75 0 01-1.02 0l-4-3.625a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </button>
            <ul v-if="showSupportDropdown" class="pl-6 mt-2 space-y-1">
              <li v-for="item in supportItems" :key="item.label">
                <Link 
                  :href="item.href" 
                  class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 transition"
                  @click="showSupportDropdown = false; showMobileMenu = false"
                >
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </li>
          
          <li>
            <Link
              :href="route('ressources.index')"
              class="block px-3 py-2 text-gray-700 font-medium hover:bg-gray-100 transition"
              @click="showMobileMenu = false"
            >
              {{ t('nav.resources') }}
            </Link>
          </li>
          <li>
            <Link
              :href="route('blogs.index')"
              class="block px-3 py-2 text-gray-700 font-medium hover:bg-gray-100 transition"
              @click="showMobileMenu = false"
            >
              {{ t('nav.blog') }}
            </Link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { ref, onMounted, computed, watch, onBeforeUnmount } from "vue";
import { resolveStorageUrl } from '@/utils/storage'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
});

const page = usePage()
const authSessionInvalidated = ref(false)
const authRedirectIssued = ref(false)

const resolvedAuthUser = computed(() => {
  const fromPage = page.props?.auth?.user || null
  const fromProp = props.authUser || null
  if (!fromPage) return fromProp
  if (!fromProp) return fromPage

  return {
    ...fromPage,
    ...fromProp,
    profile_image_url: fromProp.profile_image_url ?? fromPage.profile_image_url ?? null,
  }
})

const activeAuthUser = computed(() => {
  if (authSessionInvalidated.value) return null
  return resolvedAuthUser.value
})

const showDropdown = ref(false);
const showAboutDropdown = ref(false);
const showSupportDropdown = ref(false);
const showMobileMenu = ref(false);
const showPatientMenu = ref(false)
const showPsychologistMenu = ref(false)
const showPatientNotifications = ref(false)
const showPsychologistNotifications = ref(false)
const patientNotificationDropdownRef = ref(null)
const psychologistNotificationDropdownRef = ref(null)
const patientMenuRef = ref(null)
const psychologistMenuRef = ref(null)
const patientNotifications = ref([])
const psychologistNotifications = ref([])
const patientUnreadCount = ref(0)
const psychologistUnreadCount = ref(0)
const patientLatestNotificationId = ref(0)
const psychologistLatestNotificationId = ref(0)
const patientNotificationPollTimerRef = ref(null)
const psychologistNotificationPollTimerRef = ref(null)
const { t, locale } = useI18n();
const currentLang = ref("");

const isRtl = computed(() => {
  try {
    return (locale?.value || '').toString() === 'ar' || document.documentElement.getAttribute('dir') === 'rtl'
  } catch (e) {
    return (locale?.value || '').toString() === 'ar'
  }
})

const isPatient = computed(() => {
  const role = (activeAuthUser.value?.role ?? '').toString().trim().toUpperCase()
  return !!activeAuthUser.value && role === 'PATIENT'
})

const patientDisplayName = computed(() => {
  return activeAuthUser.value?.name || activeAuthUser.value?.email || 'Account'
})

const patientAvatarUrl = computed(() => {
  return resolveStorageUrl(activeAuthUser.value?.profile_image_url) || null
})

const patientInitials = computed(() => {
  const source = (activeAuthUser.value?.name || activeAuthUser.value?.email || 'A').trim()
  const parts = source.split(/\s+/).filter(Boolean)
  const initials = parts.slice(0, 2).map((p) => p[0]).join('')
  return (initials || 'A').toUpperCase()
})

const isPsychologist = computed(() => {
  const role = (activeAuthUser.value?.role ?? '').toString().trim().toUpperCase()
  return !!activeAuthUser.value && role === 'PSYCHOLOGIST'
})

// Pending appointments count (cart) - read from common props with fallbacks
const patientCartCount = computed(() => {
  const p = page.props || {}
  // possible server-side keys: pendingAppointmentsCount, cart.appointments, auth.user.pending_appointments_count
  const fromPending = Number(p.pendingAppointmentsCount || p.pending_appointments_count || 0)
  if (fromPending && !Number.isNaN(fromPending)) return fromPending
  const fromCart = (p.cart && Array.isArray(p.cart.appointments)) ? p.cart.appointments.length : 0
  if (fromCart) return fromCart
  const fromUser = Number(p?.auth?.user?.pending_appointments_count || 0)
  if (fromUser && !Number.isNaN(fromUser)) return fromUser
  // Fallback: scan props for any arrays of appointment-like objects and count those with pending status
  function countPendingInArray(arr) {
    if (!Array.isArray(arr)) return 0
    return arr.reduce((acc, item) => {
      if (!item || typeof item !== 'object') return acc
      const status = (item.status || item.state || item.status_name || '').toString().toLowerCase()
      if (status === 'pending' || status === 'awaiting' || status === 'scheduled') return acc + 1
      return acc
    }, 0)
  }

  let scanned = 0
  for (const key in p) {
    if (!Object.prototype.hasOwnProperty.call(p, key)) continue
    const val = p[key]
    if (Array.isArray(val)) {
      scanned += countPendingInArray(val)
    } else if (val && typeof val === 'object') {
      // look for nested arrays inside objects
      for (const k2 in val) {
        if (Array.isArray(val[k2])) scanned += countPendingInArray(val[k2])
      }
    }
  }

  if (scanned > 0) return scanned
  return 0
})

// Local badge that can be updated instantly when appointments are added/removed.
// Initialize from server-provided prop when available (including zero), otherwise from localStorage.
const stored = (() => {
  try { return Number(localStorage.getItem('pendingAppointmentsCount') || 0) } catch (e) { return 0 }
})()
const initialPatientCart = (() => {
  const v = Number(patientCartCount.value)
  if (!Number.isNaN(v)) return v
  return stored || 0
})()
const patientCartLocal = ref(initialPatientCart)
const _pulse = ref(false)

// If server provided a count on initial render, persist it to localStorage so
// it remains available on subsequent pages and logins.
try {
  const initialServer = Number(patientCartCount.value)
  if (!Number.isNaN(initialServer)) {
    try { localStorage.setItem('pendingAppointmentsCount', String(Math.max(0, initialServer))) } catch (e) {}
  }
} catch (e) {}

// When server-provided prop changes (e.g., on full page visit), sync local value
// and persist to localStorage so it survives full page navigations.
watch(patientCartCount, (v) => {
  const newVal = Number(v || 0)
  patientCartLocal.value = newVal
  try { localStorage.setItem('pendingAppointmentsCount', String(newVal)) } catch (e) {}
})

function handleAppointmentAdded(evt) {
  // optional: payload may include count
  const increment = Number(evt?.detail?.count ?? 1)
  patientCartLocal.value = Number(patientCartLocal.value || 0) + increment
  // trigger a quick pulse animation
  _pulse.value = true
  setTimeout(() => (_pulse.value = false), 700)
  try { localStorage.setItem('pendingAppointmentsCount', String(Number(patientCartLocal.value || 0))) } catch (e) {}
}

function handleAppointmentRemoved(evt) {
  const decrement = Number(evt?.detail?.count ?? 1)
  patientCartLocal.value = Math.max(0, Number(patientCartLocal.value || 0) - decrement)
  try { localStorage.setItem('pendingAppointmentsCount', String(Number(patientCartLocal.value || 0))) } catch (e) {}
}

function handleAppointmentCountUpdated(evt) {
  const cnt = Number(evt?.detail?.count ?? NaN)
  if (!Number.isNaN(cnt)) {
    patientCartLocal.value = Math.max(0, Number(cnt || 0))
    try { localStorage.setItem('pendingAppointmentsCount', String(Number(patientCartLocal.value || 0))) } catch (e) {}
  }
}

function applyPatientNotificationFeed(payload) {
  patientNotifications.value = Array.isArray(payload?.notifications) ? payload.notifications : []
  patientUnreadCount.value = Number(payload?.unread_count || 0)
  patientLatestNotificationId.value = Number(payload?.latest_id || 0)
}

function applyPsychologistNotificationFeed(payload) {
  psychologistNotifications.value = Array.isArray(payload?.notifications) ? payload.notifications : []
  psychologistUnreadCount.value = Number(payload?.unread_count || 0)
  psychologistLatestNotificationId.value = Number(payload?.latest_id || 0)
}

function clearPendingAppointmentsStorage() {
  patientCartLocal.value = 0
  try { localStorage.removeItem('pendingAppointmentsCount') } catch (e) {}
}

function resetPatientNotificationState() {
  patientNotifications.value = []
  patientUnreadCount.value = 0
  patientLatestNotificationId.value = 0
  showPatientNotifications.value = false
}

function resetPsychologistNotificationState() {
  psychologistNotifications.value = []
  psychologistUnreadCount.value = 0
  psychologistLatestNotificationId.value = 0
  showPsychologistNotifications.value = false
}

function stopAllNotificationPolling() {
  stopPatientNotificationPolling()
  stopPsychologistNotificationPolling()
}

function redirectToGuestPage() {
  if (authRedirectIssued.value || typeof window === 'undefined') return
  authRedirectIssued.value = true
  window.location.replace('/')
}

function invalidateNavbarSession(options = {}) {
  const { redirect = false } = options

  authSessionInvalidated.value = true
  showPatientMenu.value = false
  showPsychologistMenu.value = false
  showPatientNotifications.value = false
  showPsychologistNotifications.value = false
  showMobileMenu.value = false
  stopAllNotificationPolling()
  resetPatientNotificationState()
  resetPsychologistNotificationState()
  clearPendingAppointmentsStorage()

  if (redirect) {
    redirectToGuestPage()
  }
}

function isUnauthorizedNavbarError(error) {
  const status = Number(error?.response?.status || 0)
  return status === 401 || status === 419
}

function handleNavbarRequestError(error, message) {
  if (isUnauthorizedNavbarError(error)) {
    invalidateNavbarSession({ redirect: true })
    return
  }

  console.error(message, error)
}

async function clearClientSessionArtifacts() {
  clearPendingAppointmentsStorage()

  if (typeof window === 'undefined' || !('caches' in window)) return

  try {
    const cacheKeys = await window.caches.keys()
    await Promise.all(cacheKeys.map((cacheKey) => window.caches.delete(cacheKey)))
  } catch (e) {}
}

async function fetchPatientNotificationFeed() {
  if (!isPatient.value) return

  try {
    const { data } = await window.axios.get('/notifications/feed')
    applyPatientNotificationFeed(data)
  } catch (error) {
    handleNavbarRequestError(error, 'Failed to fetch patient notifications feed')
  }
}

async function fetchPsychologistNotificationFeed() {
  if (!isPsychologist.value) return

  try {
    const { data } = await window.axios.get('/notifications/feed')
    applyPsychologistNotificationFeed(data)
  } catch (error) {
    handleNavbarRequestError(error, 'Failed to fetch psychologist notifications feed')
  }
}

async function fetchPatientNotificationLite() {
  if (!isPatient.value) return

  try {
    const { data } = await window.axios.get('/notifications/feed?lite=1')
    const unread = Number(data?.unread_count || 0)
    const latest = Number(data?.latest_id || 0)
    const hasChanges = latest > patientLatestNotificationId.value

    patientUnreadCount.value = unread

    if (hasChanges || showPatientNotifications.value) {
      await fetchPatientNotificationFeed()
    }
  } catch (error) {
    handleNavbarRequestError(error, 'Failed to fetch patient notifications lite feed')
  }
}

async function fetchPsychologistNotificationLite() {
  if (!isPsychologist.value) return

  try {
    const { data } = await window.axios.get('/notifications/feed?lite=1')
    const unread = Number(data?.unread_count || 0)
    const latest = Number(data?.latest_id || 0)
    const hasChanges = latest > psychologistLatestNotificationId.value

    psychologistUnreadCount.value = unread

    if (hasChanges || showPsychologistNotifications.value) {
      await fetchPsychologistNotificationFeed()
    }
  } catch (error) {
    handleNavbarRequestError(error, 'Failed to fetch psychologist notifications lite feed')
  }
}

function startPatientNotificationPolling() {
  stopPatientNotificationPolling()
  if (!isPatient.value) return

  patientNotificationPollTimerRef.value = setInterval(() => {
    if (document.visibilityState !== 'visible') return
    fetchPatientNotificationLite()
  }, 3000)
}

function startPsychologistNotificationPolling() {
  stopPsychologistNotificationPolling()
  if (!isPsychologist.value) return

  psychologistNotificationPollTimerRef.value = setInterval(() => {
    if (document.visibilityState !== 'visible') return
    fetchPsychologistNotificationLite()
  }, 3000)
}

function stopPatientNotificationPolling() {
  if (!patientNotificationPollTimerRef.value) return
  clearInterval(patientNotificationPollTimerRef.value)
  patientNotificationPollTimerRef.value = null
}

function stopPsychologistNotificationPolling() {
  if (!psychologistNotificationPollTimerRef.value) return
  clearInterval(psychologistNotificationPollTimerRef.value)
  psychologistNotificationPollTimerRef.value = null
}

function handlePatientNotificationVisibilityChange() {
  if (document.visibilityState !== 'visible') return
  if (isPatient.value) fetchPatientNotificationLite()
  if (isPsychologist.value) fetchPsychologistNotificationLite()
}

function handlePatientNotificationWindowFocus() {
  if (isPatient.value) fetchPatientNotificationLite()
  if (isPsychologist.value) fetchPsychologistNotificationLite()
}

function togglePatientNotifications() {
  showPatientNotifications.value = !showPatientNotifications.value
  showPsychologistNotifications.value = false
  showPatientMenu.value = false
  showPsychologistMenu.value = false

  if (showPatientNotifications.value) {
    fetchPatientNotificationFeed()
  }
}

function togglePsychologistNotifications() {
  showPsychologistNotifications.value = !showPsychologistNotifications.value
  showPatientNotifications.value = false
  showPatientMenu.value = false
  showPsychologistMenu.value = false

  if (showPsychologistNotifications.value) {
    fetchPsychologistNotificationFeed()
  }
}

function closePatientNotifications() {
  showPatientNotifications.value = false
}

function closePsychologistNotifications() {
  showPsychologistNotifications.value = false
}

function handlePatientNotificationOutsideClick(event) {
  const patientRoot = patientNotificationDropdownRef.value
  if (patientRoot && !patientRoot.contains(event.target)) {
    closePatientNotifications()
  }

  const psychologistRoot = psychologistNotificationDropdownRef.value
  if (psychologistRoot && !psychologistRoot.contains(event.target)) {
    closePsychologistNotifications()
  }
  
  const patientMenu = patientMenuRef.value
  if (patientMenu && !patientMenu.contains(event.target)) {
    showPatientMenu.value = false
  }

  const psychologistMenu = psychologistMenuRef.value
  if (psychologistMenu && !psychologistMenu.contains(event.target)) {
    showPsychologistMenu.value = false
  }
}

async function openPatientNotification(notification) {
  if (!notification) return

  if (!notification.is_read) {
    try {
      const { data } = await window.axios.post(`/notifications/${notification.id}/read`)
      applyPatientNotificationFeed(data)
    } catch (error) {
      handleNavbarRequestError(error, 'Failed to mark patient notification as read')
    }
  }
}

async function markAllPatientNotificationsAsRead() {
  if (!isPatient.value || patientUnreadCount.value === 0) return

  try {
    const { data } = await window.axios.post('/notifications/read-all')
    applyPatientNotificationFeed(data)
  } catch (error) {
    handleNavbarRequestError(error, 'Failed to mark all patient notifications as read')
  }
}

async function openPsychologistNotification(notification) {
  if (!notification) return

  if (!notification.is_read) {
    try {
      const { data } = await window.axios.post(`/notifications/${notification.id}/read`)
      applyPsychologistNotificationFeed(data)
    } catch (error) {
      handleNavbarRequestError(error, 'Failed to mark psychologist notification as read')
    }
  }
}

async function markAllPsychologistNotificationsAsRead() {
  if (!isPsychologist.value || psychologistUnreadCount.value === 0) return

  try {
    const { data } = await window.axios.post('/notifications/read-all')
    applyPsychologistNotificationFeed(data)
  } catch (error) {
    handleNavbarRequestError(error, 'Failed to mark all psychologist notifications as read')
  }
}

function viewAllPatientNotifications() {
  closePatientNotifications()
  router.visit('/notifications')
}

function viewAllPsychologistNotifications() {
  closePsychologistNotifications()
  router.visit('/notifications')
}

onMounted(() => {
  window.addEventListener('appointment:added', handleAppointmentAdded)
  window.addEventListener('appointment:removed', handleAppointmentRemoved)
  window.addEventListener('appointment:count-updated', handleAppointmentCountUpdated)
  document.addEventListener('click', handlePatientNotificationOutsideClick)
  document.addEventListener('visibilitychange', handlePatientNotificationVisibilityChange)
  window.addEventListener('focus', handlePatientNotificationWindowFocus)

  if (isPatient.value) {
    fetchPatientNotificationFeed().then(() => startPatientNotificationPolling())
  }

  if (isPsychologist.value) {
    fetchPsychologistNotificationFeed().then(() => startPsychologistNotificationPolling())
  }
})

onBeforeUnmount(() => {
  window.removeEventListener('appointment:added', handleAppointmentAdded)
  window.removeEventListener('appointment:removed', handleAppointmentRemoved)
  window.removeEventListener('appointment:count-updated', handleAppointmentCountUpdated)
  document.removeEventListener('click', handlePatientNotificationOutsideClick)
  document.removeEventListener('visibilitychange', handlePatientNotificationVisibilityChange)
  window.removeEventListener('focus', handlePatientNotificationWindowFocus)
  stopPatientNotificationPolling()
  stopPsychologistNotificationPolling()
})

const psychologistDisplayName = computed(() => {
  return activeAuthUser.value?.name || activeAuthUser.value?.email || 'Account'
})

const psychologistAvatarUrl = computed(() => {
  return resolveStorageUrl(activeAuthUser.value?.profile_image_url) || null
})

const psychologistInitials = computed(() => {
  const source = (activeAuthUser.value?.name || activeAuthUser.value?.email || 'A').trim()
  const parts = source.split(/\s+/).filter(Boolean)
  const initials = parts.slice(0, 2).map((p) => p[0]).join('')
  return (initials || 'A').toUpperCase()
})

// Languages list
const languages = [
  { code: "fr", label: "🇫🇷 Français" },
  { code: "en", label: "🇬🇧 English" },
  { code: "ar", label: "🇸🇦 العربية" },
];

// Set language
function setLang(lang) {
  locale.value = lang;
  currentLang.value = languages.find((l) => l.code === lang).label;
  localStorage.setItem("locale", lang);
  showDropdown.value = false;
  showPatientMenu.value = false
  showPsychologistMenu.value = false
  showPatientNotifications.value = false
  showPsychologistNotifications.value = false
  
  // Set document direction based on language
  if (lang === 'ar') {
    document.documentElement.setAttribute('dir', 'rtl');
    document.documentElement.setAttribute('lang', 'ar');
  } else {
    document.documentElement.setAttribute('dir', 'ltr');
    document.documentElement.setAttribute('lang', lang);
  }
}

// Load saved language
onMounted(() => {
  const savedLang = localStorage.getItem("locale") || locale.value;
  setLang(savedLang);
});

// About dropdown items reactive to language
const aboutItems = computed(() => [
  { label: t("nav.aboutItems.0"), href: route('telemental-health') },
  { label: t("nav.aboutItems.1"), href: route('who-we-are') },
  { label: t("nav.aboutItems.2"), href: route('our-care-team') },
  { label: t("nav.aboutItems.3"), href: route('join-our-team') },
  { label: t("nav.aboutItems.4"), href: route('terms-conditions') },
  { label: t("nav.aboutItems.5"), href: route('privacy-protection') }
]);

// Support dropdown items reactive to language
const supportItems = computed(() => [
  { label: t("nav.supportItems.0"), href: route('faq') },
  { label: t("nav.supportItems.1"), href: route('how-it-works') }
]);

async function handlePatientLogout(e) {
  try {
    invalidateNavbarSession()
    await clearClientSessionArtifacts()
    router.post(route('logout'), {}, {
      preserveScroll: false,
      preserveState: false,
      replace: true,
      onSuccess: () => redirectToGuestPage(),
      onError: () => redirectToGuestPage(),
      onCancel: () => redirectToGuestPage(),
    })
  } finally {
    // hide menu after request starts/completes
    showPatientMenu.value = false
  }
}

async function handlePsychologistLogout(e) {
  try {
    invalidateNavbarSession()
    await clearClientSessionArtifacts()
    router.post(route('logout'), {}, {
      preserveScroll: false,
      preserveState: false,
      replace: true,
      onSuccess: () => redirectToGuestPage(),
      onError: () => redirectToGuestPage(),
      onCancel: () => redirectToGuestPage(),
    })
  } finally {
    showPsychologistMenu.value = false
  }
}
</script>

<style scoped>
nav ul li a {
  position: relative;
}
nav ul li a::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: #5997ac;
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s;
}
nav ul li:hover > a::before {
  transform: scaleX(1);
}

/* RTL Support for Arabic */
[dir="rtl"] nav ul li a::before {
  transform-origin: right;
}
</style>