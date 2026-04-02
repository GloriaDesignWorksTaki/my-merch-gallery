import axios from 'axios';
import { onMounted, ref, type Ref, watch } from 'vue';

export type MerchItemOption = { id: number; name: string };

type Params = {
  bandId: Ref<string | number | null | undefined>;
  merchItemId: Ref<string | number | null | undefined>;
  /**
   * サーバから既に受け取っている場合に、マウント直後の再取得をスキップするための初期値。
   * 例: Edit 画面で props.merchItems を受け取っている場合。
   */
  initialItems?: MerchItemOption[];
  /**
   * マウント直後に fetch するか。
   * デフォルト `true`（Create 画面などで必要）。
   */
  fetchOnMount?: boolean;
};

export function useMerchItemOptions({
  bandId,
  merchItemId,
  initialItems = [],
  fetchOnMount = true,
}: Params) {
  const merchItems = ref<MerchItemOption[]>(initialItems);
  const loading = ref(false);
  let currentRequestId = 0;

  const validateSelected = () => {
    if (merchItemId.value === null || merchItemId.value === undefined || merchItemId.value === '') {
      return;
    }

    const exists = merchItems.value.some((item) => String(item.id) === String(merchItemId.value));
    if (!exists) {
      merchItemId.value = '';
    }
  };

  const load = async () => {
    const nextBandId = bandId.value;

    if (nextBandId === null || nextBandId === undefined || nextBandId === '') {
      merchItems.value = [];
      merchItemId.value = '';
      return;
    }

    const requestId = ++currentRequestId;
    loading.value = true;
    try {
      const response = await axios.get(route('bands.merch-items.options', nextBandId));

      // 別の帯に切り替えた後で古いレスポンスが返ってきた場合は無視する
      if (requestId !== currentRequestId) {
        return;
      }

      merchItems.value = (response.data?.merchItems ?? []) as MerchItemOption[];

      validateSelected();
    } catch (e) {
      if (requestId !== currentRequestId) {
        return;
      }

      // オプション取得失敗時は「選択を安全側に倒す」
      merchItems.value = [];
      merchItemId.value = '';
      console.error(e);
    } finally {
      if (requestId === currentRequestId) {
        loading.value = false;
      }
    }
  };

  if (fetchOnMount) {
    watch(bandId, load, { immediate: true });
  } else {
    onMounted(validateSelected);
    watch(bandId, load);
  }

  return { merchItems, loading, load };
}

