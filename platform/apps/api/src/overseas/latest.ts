export const LATEST_ORDER = [
  { createdAt: 'desc' as const },
  { sourceLegacyId: { sort: 'desc' as const, nulls: 'last' as const } },
];

export const LATEST_RULES = {
  orderBy: 'created_at DESC, source_legacy_id DESC NULLS LAST',
  implicitStatusFilter: false,
  duplicatesAllowed: true,
  hardDelete: false,
} as const;
