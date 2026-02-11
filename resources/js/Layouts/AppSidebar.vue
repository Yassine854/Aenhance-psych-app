<template>
  <aside
    :class="[
      'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 dark:text-gray-300 h-screen transition-all duration-300 ease-in-out z-50 border-r border-gray-200 pointer-events-auto',
      {
        'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
        'lg:w-[90px]': !isExpanded && !isHovered,
        'translate-x-0 w-[290px]': isMobileOpen,
        '-translate-x-full': !isMobileOpen,
        'lg:translate-x-0': true,
      },
    ]"
    @mouseenter="!isExpanded && (isHovered = true)"
    @mouseleave="isHovered = false"
  >
  <div class="py-8 flex justify-center items-center transition-[justify-content,padding] duration-300 ease-in-out">
    <Link href="/">
      <img
        src="/storage/aenhance.svg"
        alt="Logo"
        class="block"
        style="width: 120px; height: 90px; object-fit: contain;"
      />
    </Link>
  </div>

    <div
      class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar"
    >
      <nav class="mb-6">
        <div class="flex flex-col gap-4">
          <div v-for="(menuGroup, groupIndex) in filteredMenuGroups" :key="groupIndex">
            <h2
              :class="[
                'mb-4 text-xs uppercase flex leading-[20px] text-gray-400',
                !isExpanded && !isHovered
                  ? 'lg:justify-center'
                  : 'justify-start',
              ]"
            >
              <template v-if="isExpanded || isHovered || isMobileOpen">
                {{ menuGroup.title }}
              </template>
              <HorizontalDots v-else />
            </h2>
            <ul class="flex flex-col gap-4">
              <li v-for="(item, index) in menuGroup.items" :key="groupIndex + '-' + index">
                <!-- Professional Dropdown Implementation -->
                <div v-if="item.subItems">
                  <button
                    @click="handleParentClick(item)"
                    :aria-expanded="openSubmenu === item.name"
                    :aria-controls="`dropdown-${item.name}`"
                    class="menu-item group w-full flex items-center px-3 py-2 rounded transition text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-500"
                  >
                    <span class="w-9 h-9 flex items-center justify-center">
                      <component :is="item.icon" />
                    </span>
                    <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text">{{ item.name }}</span>
                    <ChevronDownIcon
                      v-if="isExpanded || isHovered || isMobileOpen"
                      :class="[
                        'ml-auto w-5 h-5 transition-transform duration-200',
                        openSubmenu === item.name ? 'rotate-180 text-brand-500' : '',
                      ]"
                    />
                  </button>
                  <transition name="fade">
                    <ul
                      v-if="openSubmenu === item.name"
                      :id="`dropdown-${item.name}`"
                      class="mt-2 space-y-1 ml-6 bg-white/95 dark:bg-gray-900/80 backdrop-blur-sm rounded-lg shadow-md ring-1 ring-gray-100 dark:ring-gray-800 py-2 divide-y divide-gray-100 dark:divide-gray-800"
                      role="menu"
                    >
                      <li v-for="subItem in item.subItems" :key="subItem.name" role="none">
                        <Link
                          :href="subItem.path"
                          :class="[
                              'relative flex items-center gap-3 px-4 py-2 transition-transform duration-150 ease-in-out text-gray-700 dark:text-gray-300',
                              (isActive(subItem.path) || pressedItem === subItem.path)
                                ? 'bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-semibold border-l-4 border-brand-500 pl-3'
                                : 'hover:translate-x-1 hover:text-brand-600 dark:hover:text-brand-400',
                            ]"
                            @click="pressItem(item.name, subItem.path)"
                            role="menuitem"
                            tabindex="0"
                        >
                          <span class="w-2 h-2 rounded-full bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                          <span class="truncate">{{ subItem.name }}</span>
                        </Link>
                      </li>
                    </ul>
                  </transition>
                </div>
                <!-- End Professional Dropdown -->
                <!-- Render non-dropdown (direct) items -->
                <div v-else>
                  <Link
                    :href="item.path"
                    :class="[
                        'relative flex items-center gap-3 px-3 py-2 transition-transform duration-150 ease-in-out text-gray-700 dark:text-gray-300',
                        (isActive(item.path) || pressedItem === item.path)
                          ? 'bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-semibold border-l-4 border-brand-500 pl-3'
                          : 'hover:translate-x-1 hover:text-brand-600 dark:hover:text-brand-400',
                      ]"
                    @click="pressItem(item.name, item.path)"
                  >
                    <span class="w-9 h-9 flex items-center justify-center">
                      <component :is="item.icon" />
                    </span>
                    <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text">{{ item.name }}</span>
                  </Link>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <SidebarWidget v-if="isExpanded || isHovered || isMobileOpen" />
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link } from '@inertiajs/vue3'

import {
  GridIcon,
  CalenderIcon,
  UserCircleIcon,
  ChatIcon,
  MailIcon,
  DocsIcon,
  PieChartIcon,
  ChevronDownIcon,
  HorizontalDots,
  PageIcon,
  TableIcon,
  ListIcon,
  PlugInIcon,
} from "../../../vue-tailwind-admin-dashboard-main/src/icons";
import SidebarWidget from "./SidebarWidget.vue";
import BoxCubeIcon from "@/icons/BoxCubeIcon.vue";
import { useSidebar } from "@/composables/useSidebar";
import { ref as vueRef } from "vue";

const sidebarState = useSidebar() || {};
const isExpanded = sidebarState.isExpanded ?? vueRef(false);
const isMobileOpen = sidebarState.isMobileOpen ?? vueRef(false);
const isHovered = sidebarState.isHovered ?? vueRef(false);
const openSubmenu = sidebarState.openSubmenu ?? vueRef(null);
import { usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'

const page = usePage()
// pressedItem highlights clicked subitem until navigation updates the page URL
const pressedItem = ref(null);
const pressItem = (parentName, path) => {
  pressedItem.value = path;
  // ensure the parent dropdown stays open
  try {
    if (openSubmenu && typeof openSubmenu === 'object' && 'value' in openSubmenu) {
      openSubmenu.value = parentName;
    }
  } catch (e) {
    // ignore if openSubmenu is not writable
  }
};

const handleParentClick = (item) => {
  try {
    // if the parent item defines a path, navigate to it and keep submenu open
    if (item && item.path) {
      if (openSubmenu && typeof openSubmenu === 'object' && 'value' in openSubmenu) {
        openSubmenu.value = item.name;
      }
      Inertia.get(item.path)
      return
    }
  } catch (e) {
    // fallback to toggle
  }

  // otherwise toggle submenu open/close
  try {
    openSubmenu.value = openSubmenu.value === item.name ? null : item.name
  } catch (e) {
    // noop
  }
}

watch(() => page.url, () => {
  pressedItem.value = null;
});
const currentRole = computed(() => {
  return (
    page.props?.value?.auth?.user?.role ?? page.props?.auth?.user?.role ?? null
  )
})

const menuGroups = [
  {
    title: "Menu",
    items: [
      {
        icon: GridIcon,
        name: "Dashboard",
        subItems: [{ name: "Ecommerce", path: "/", pro: false }],
        // visible to all
      },
      // Admin: direct links for psychologists (use same style as other items)


      {
        name: "Psychologists",
        icon: ListIcon,
        roles: ['ADMIN'],
        subItems: [
          { name: "All Psychologists", path: "/psychologist-profiles", pro: false },
              { name: "Payouts", path: "/admin/payouts", pro: false },
          { name: "Specialisations", path: "/specialisations", pro: false },
          { name: "Expertises", path: "/expertises", pro: false },
        ],
      },

      {
        name: "Appointments",
        icon: CalenderIcon,
        roles: ['ADMIN'],
        path: "/admin/appointments",
      },
      {
        name: "Payments",
        icon: TableIcon,
        roles: ['ADMIN'],
        path: "/admin/payments",
      },
      // Psychologist: self profile entry
      {
        name: "My Profile",
        icon: UserCircleIcon,
        roles: ['PSYCHOLOGIST'],
        path: "/psychologist/profile",
      },
      {
        name: "Appointments",
        icon: CalenderIcon,
        roles: ['PSYCHOLOGIST'],
        path: "/psychologist/appointments",
      },
      {
        name: "Patients",
        icon: ListIcon,
        roles: ['ADMIN', 'PSYCHOLOGIST'], 
        subItems: [
          { name: "Patient Records", path: "/form-elements", pro: false },
          { name: "All Patients", path: "/patient-profiles", pro: false },
          { name: "Payment follow-up", path: "/form-elements", pro: false },
        ],
      },
      {
        name: "Page Settings",
        icon: ListIcon,
        roles: ['ADMIN'],
        subItems: [
          { name: "Ressources", path: "/form-elements", pro: false },
          { name: "Blogs", path: "/form-elements", pro: false },
          { name: "App Fee", path: "/app-fees", pro: false },
        ],
      },
      {
        name: "Logs",
        icon: DocsIcon,
        roles: ['ADMIN'],
        subItems: [
          { name: "Appointments", path: "/admin/logs/appointments", pro: false },
          { name: "Sessions", path: "/admin/logs/sessions", pro: false },
          { name: "Psychologists", path: "/admin/logs/psychologists", pro: false },
          { name: "Patients", path: "/admin/logs/patients", pro: false },
       
        ],
      },
      // ... Add other menu items here
    ],
  },
];

const isItemVisible = (item) => {
  if (!item.roles || !Array.isArray(item.roles)) return true
  let role = currentRole.value
  if (!role) return false
  role = String(role).trim().toUpperCase()
  // normalize allowed roles to uppercase for case-insensitive comparison
  const allowed = item.roles.map(r => String(r).trim().toUpperCase())
  return allowed.includes(role)
}

const filteredMenuGroups = computed(() => {
  return menuGroups
    .map((g) => ({ ...g, items: g.items.filter((i) => isItemVisible(i)) }))
    .filter((g) => g.items.length)
})

const isActive = (path) => {
  // Support both exact and partial match for active state
  if (!path) return false;
  const current = window.location.pathname;
  // Exact match or starts with path (for nested routes)
  return current === path || current.startsWith(path + '/');
}

const toggleSubmenu = (groupIndex, itemIndex) => {
  const key = `${groupIndex}-${itemIndex}`;
  openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isAnySubmenuRouteActive = computed(() => {
  return menuGroups.some((group) =>
    group.items.some(
      (item) =>
        item.subItems && item.subItems.some((subItem) => isActive(subItem.path))
    )
  );
});

const isSubmenuOpen = (groupIndex, itemIndex) => {
  const key = `${groupIndex}-${itemIndex}`;
  // Only open if toggled, not automatically by route
  return openSubmenu.value === key;
};


</script>