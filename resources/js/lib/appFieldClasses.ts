export type FieldSize = 'sm' | 'md' | 'lg';
export type FieldRadius = 'sm' | 'md' | 'full';
export type FieldVariant = 'default' | 'error';

const sizeClasses: Record<FieldSize, string> = {
  /* md 未満で text-sm だと iOS Safari がフォーカス時にページをズームするため、小画面は 16px 相当を維持 */
  sm: 'min-h-9 px-3 py-2 text-base md:text-sm',
  md: 'min-h-11 px-4 py-3 text-base',
  lg: 'min-h-12 px-4 py-3.5 text-lg',
};

const radiusClasses: Record<FieldRadius, string> = {
  sm: 'rounded-xl',
  md: 'rounded-2xl',
  full: 'rounded-full',
};

const variantClasses: Record<FieldVariant, string> = {
  default:
    'glass-panel border-white/50 bg-white/45 shadow-[0_10px_30px_rgba(125,166,214,0.12)] focus:border-sky-300/70 focus:ring-2 focus:ring-sky-200/60',
  error:
    'border border-red-400/70 bg-white/50 shadow-sm focus:border-red-500 focus:ring-2 focus:ring-red-200/60',
};

const fieldBase =
  'w-full text-slate-800 placeholder:text-slate-400 transition focus:outline-none disabled:cursor-not-allowed disabled:opacity-60';

export type AppFieldClassOptions = {
  variant?: FieldVariant;
  size?: FieldSize;
  radius?: FieldRadius;
  extraClass?: string;
};

export function buildAppFieldClass(options: AppFieldClassOptions): string {
  const variant = options.variant ?? 'default';
  const size = options.size ?? 'md';
  const radius = options.radius ?? 'md';

  return [
    fieldBase,
    variantClasses[variant],
    sizeClasses[size],
    radiusClasses[radius],
    options.extraClass ?? '',
  ]
    .filter(Boolean)
    .join(' ');
}
