/**
 * Laravel LengthAwarePaginator の `links` 配列要素（Inertia が JSON で渡す形）
 */
export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

/** 一覧ページでよく使う「data + links」だけの最小形（各ページで meta を足す場合は交差型で） */
export type PaginatedList<T> = {
  data: T[];
  links: PaginationLink[];
};
