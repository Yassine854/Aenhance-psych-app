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
              <li v-for="(item, index) in menuGroup.items" :key="item.name">
                <button
                  v-if="item.subItems"
                  @click="toggleSubmenu(groupIndex, index)"
                  :class="[
                    'menu-item group w-full text-gray-700 dark:text-gray-300',
                    {
                      'menu-item-active bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100': isSubmenuOpen(groupIndex, index),
                      'menu-item-inactive': !isSubmenuOpen(groupIndex, index),
                    },
                    !isExpanded && !isHovered
                      ? 'lg:px-0 lg:justify-center'
                      : 'lg:px-3 lg:justify-start',
                  ]"
                >
                  <span
                    :class="[
                      'w-9 h-9 flex items-center justify-center',
                      isSubmenuOpen(groupIndex, index)
                        ? 'menu-item-icon-active text-gray-700 dark:text-gray-100'
                        : 'menu-item-icon-inactive text-gray-500 dark:text-gray-400',
                    ]"
                  >
                    <component :is="item.icon" />
                  </span>
                  <span
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="menu-item-text"
                    >{{ item.name }}</span
                  >
                  <ChevronDownIcon
                    v-if="isExpanded || isHovered || isMobileOpen"
                    :class="[
                      'ml-auto w-5 h-5 transition-transform duration-200',
                      {
                        'rotate-180 text-brand-500': isSubmenuOpen(
                          groupIndex,
                          index
                        ),
                      },
                    ]"
                  />
                </button>

                <Link
                  v-else-if="item.path"
                  :href="item.path"
                  :class="[
                    'menu-item group text-gray-700 dark:text-gray-300',
                    {
                      'menu-item-active bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100': isActive(item.path),
                      'menu-item-inactive': !isActive(item.path),
                    },
                  ]"
                >
                  <span
                    :class="[
                      'w-9 h-9 flex items-center justify-center',
                      'w-9 h-9 flex items-center justify-center',
                      isActive(item.path)
                        ? 'menu-item-icon-active text-gray-700 dark:text-gray-100'
                        : 'menu-item-icon-inactive text-gray-500 dark:text-gray-400',
                    ]"
                  >
                    <component :is="item.icon" />
                  </span>
                  <span
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="menu-item-text"
                    >{{ item.name }}</span
                  >
                </Link>

                <div
                  v-if="
                    isSubmenuOpen(groupIndex, index) &&
                    (isExpanded || isHovered || isMobileOpen)
                  "
                >
                  <ul class="mt-2 space-y-1 ml-9">
                    <li v-for="subItem in item.subItems" :key="subItem.name">
                      <Link
                        :href="subItem.path"
                        :class="[
                          'menu-dropdown-item text-gray-700 dark:text-gray-300',
                          {
                            'menu-dropdown-item-active bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100': isActive(
                              subItem.path
                            ),
                            'menu-dropdown-item-inactive': !isActive(
                              subItem.path
                            ),
                          },
                        ]"
                      >
                        {{ subItem.name }}
                        <span class="flex items-center gap-1 ml-auto">
                          <span
                            v-if="subItem.new"
                            :class="[
                              'menu-dropdown-badge bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200',
                              {
                                'menu-dropdown-badge-active': isActive(
                                  subItem.path
                                ),
                                'menu-dropdown-badge-inactive': !isActive(
                                  subItem.path
                                ),
                              },
                            ]"
                          >
                            new
                          </span>
                          <span
                            v-if="subItem.pro"
                            :class="[
                              'menu-dropdown-badge bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200',
                              {
                                'menu-dropdown-badge-active': isActive(
                                  subItem.path
                                ),
                                'menu-dropdown-badge-inactive': !isActive(
                                  subItem.path
                                ),
                              },
                            ]"
                          >
                            pro
                          </span>
                        </span>
                      </Link>
                    </li>
                  </ul>
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
import { ref, computed } from "vue";
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

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();
import { usePage } from '@inertiajs/vue3'

const page = usePage()
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
        path: "/psychologist-profiles",
      },
      // Psychologist: self profile entry
      {
        name: "My Profile",
        icon: UserCircleIcon,
        roles: ['PSYCHOLOGIST'],
        path: "/psychologist/profile",
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
        ],
      },
      // ... Add other menu items here
    ],
  },
];

const isItemVisible = (item) => {
  if (!item.roles || !Array.isArray(item.roles)) return true
  const role = currentRole.value
  if (!role) return false
  return item.roles.includes(role)
}

const filteredMenuGroups = computed(() => {
  return menuGroups
    .map((g) => ({ ...g, items: g.items.filter((i) => isItemVisible(i)) }))
    .filter((g) => g.items.length)
})

const isActive = (path) => window.location.pathname === path

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
  return (
    openSubmenu.value === key ||
    (isAnySubmenuRouteActive.value &&
      menuGroups[groupIndex].items[itemIndex].subItems?.some((subItem) =>
        isActive(subItem.path)
      ))
  );
};


</script>