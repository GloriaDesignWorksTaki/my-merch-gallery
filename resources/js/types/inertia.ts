export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

export type PaginatedList<T> = {
  data: T[];
  links: PaginationLink[];
};
