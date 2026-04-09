/** 左ペイン閲覧ナビのアイコン（Heroicons キー） */
export type SidebarNavIcon = 'home' | 'bands' | 'merch' | 'dashboard';

/** サイドバー「閲覧」ナビの1行 */
export type SidebarNavItem = {
  label: string;
  href: string;
  active: boolean;
  method?: 'get' | 'post';
  as?: 'button';
  icon?: SidebarNavIcon;
};

export type SidebarNavSection = {
  title: string;
  items: SidebarNavItem[];
  compact?: boolean;
  scrollable?: boolean;
};

/** CTA ボタン用アイコン */
export type SidebarCtaIcon = 'login' | 'bandRegister' | 'merchRegister' | 'signup';

export type SidebarCtaAction = {
  label: string;
  href?: string;
  useRegisterModal?: boolean;
  icon?: SidebarCtaIcon;
};

/** フッターメニュー1行のアイコン */
export type FooterMenuIcon = 'bandRegister' | 'merchRegister' | 'profile' | 'admin' | 'logout';

export type FooterMenuItem = {
  label: string;
  href: string;
  method?: 'get' | 'post';
  as?: 'button';
  danger?: boolean;
  icon?: FooterMenuIcon;
};

export type FooterMenuPlacement = 'bottom-start' | 'bottom-end' | 'top-start' | 'top-end';
