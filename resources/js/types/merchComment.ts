export type MerchCommentUser = {
  id: number;
  name: string;
  username: string;
  avatar_path?: string | null;
  avatar_url?: string | null;
  avatar_focus_x?: number | null;
  avatar_focus_y?: number | null;
  avatar_zoom?: number | null;
};

export type MerchCommentNode = {
  id: number;
  body: string;
  created_at: string;
  likes_count: number;
  liked?: boolean;
  user: MerchCommentUser;
  replies?: MerchCommentNode[];
};
