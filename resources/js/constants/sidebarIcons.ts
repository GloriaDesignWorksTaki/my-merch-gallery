import type { Component } from 'vue';
import {
  IconHome,
  IconKey,
  IconLayoutDashboard,
  IconLogout,
  IconMusic,
  IconPlus,
  IconShieldCheck,
  IconShirt,
  IconUserCircle,
  IconUserPlus,
} from '@tabler/icons-vue';
import type { FooterMenuIcon, SidebarCtaIcon, SidebarNavIcon } from '@/types/sidebar';

export const sidebarNavIcons: Record<SidebarNavIcon, Component> = {
  home: IconHome,
  bands: IconMusic,
  merch: IconShirt,
  dashboard: IconLayoutDashboard,
};

export const footerMenuIcons: Record<FooterMenuIcon, Component> = {
  profile: IconUserCircle,
  admin: IconShieldCheck,
  logout: IconLogout,
};

export const sidebarCtaIcons: Record<SidebarCtaIcon, Component> = {
  login: IconKey,
  bandRegister: IconPlus,
  merchRegister: IconPlus,
  signup: IconUserPlus,
};
