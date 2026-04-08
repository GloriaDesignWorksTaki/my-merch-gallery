export type AppTheme = 'light' | 'dark' | 'primary';

export interface AuthUser {
  id: number;
  name: string;
  username: string;
  bio?: string | null;
  avatar_path?: string | null;
  avatar_url?: string | null;
  avatar_focus_x?: number;
  avatar_focus_y?: number;
  avatar_zoom?: number;
  role?: string;
  theme?: AppTheme;
}

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  auth: {
    user: AuthUser | null;
  };
};
