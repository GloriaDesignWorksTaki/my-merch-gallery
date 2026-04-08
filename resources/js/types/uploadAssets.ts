/**
 * 公開アップロードの JSON 形（Laravel が image_url を付与）
 */
export type CoverImageJson = {
  image_path: string;
  image_url: string;
  alt_text?: string | null;
};

export type MerchImageJson = {
  id: number;
  image_path: string;
  image_url: string;
  alt_text: string | null;
};

export type BandListImageJson = {
  image_path?: string | null;
  image_url?: string | null;
};
