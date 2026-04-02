/**
 * Inertia 共有 `auth.user`。メール・認証状態は載せず、プロフィール画面は専用プロップで渡す。
 */
export interface AuthUser {
  id: number;
  name: string;
  username: string;
  bio?: string | null;
  avatar_path?: string | null;
  avatar_focus_x?: number;
  avatar_focus_y?: number;
  avatar_zoom?: number;
  role?: string;
}

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  auth: {
    user: AuthUser | null;
  };
};
