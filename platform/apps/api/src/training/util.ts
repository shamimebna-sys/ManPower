export function isUniqueConflict(error: unknown): boolean {
  return typeof error === 'object' && error !== null && 'code' in error && error.code === 'P2002';
}

export function toBigInt(value: string | undefined): bigint | undefined {
  if (value === undefined) return undefined;
  return BigInt(value);
}
