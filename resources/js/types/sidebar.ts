/** サイドバー「閲覧」ナビの1行 */
export type SidebarNavItem = {
  label: string;
  href: string;
  active: boolean;
  method?: 'get' | 'post';
  as?: 'button';
};

export type SidebarNavSection = {
  title: string;
  items: SidebarNavItem[];
  compact?: boolean;
  scrollable?: boolean;
};

export type SidebarCtaAction = {
  label: string;
  href?: string;
  useRegisterModal?: boolean;
};

export type FooterMenuItem = {
  label: string;
  href: string;
  method?: 'get' | 'post';
  as?: 'button';
  danger?: boolean;
};

export type FooterMenuPlacement = 'bottom-start' | 'bottom-end' | 'top-start' | 'top-end';
