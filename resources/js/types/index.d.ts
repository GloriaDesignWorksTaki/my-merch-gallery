export interface User {
  id: number;
  name: string;
  username: string;
  email: string;
  email_verified_at?: string;
  bio?: string | null;
  avatar_path?: string | null;
  role?: string;
}

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  auth: {
    user: User | null;
  };
};
