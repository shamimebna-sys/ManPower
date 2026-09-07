export type WalletOwnerType = 'AGENT' | 'SUB_AGENT' | 'TEACHER';
export type PaymentRequestStatus = 'P' | 'A' | 'R';
export type JournalStatus = 'POSTED' | 'REVERSED';
export type JournalEntryType =
  | 'WALLET_DEPOSIT'
  | 'FEE_APPROVAL'
  | 'FEE_REJECTION_ZERO'
  | 'OPENING_BALANCE'
  | 'REVERSAL';

export interface WalletRecord {
  id: string;
  ownerType: WalletOwnerType;
  agentId: string | null;
  subAgentId: string | null;
  teacherId: string | null;
  availableEur: string;
  projectedAvailableEur: string | null;
  ledgerAuthoritative: true;
}

export interface WalletListResult {
  items: WalletRecord[];
}

export interface PaymentRequestRecord {
  id: string;
  status: string;
  candidateId: string | null;
  walletId: string;
  amount: string;
  billTitle: string;
  billTypeCode: string | null;
  legacyBillCode: number | null;
  journalId: string | null;
  sourceLegacyId: string | null;
  createdAt: string;
}

export interface PaymentRequestListResult {
  items: PaymentRequestRecord[];
}

export interface WalletDepositRecord {
  id: string;
  status: string;
  walletId: string;
  amount: string;
  currencyCode: string;
  fxRate: string | null;
  journalId: string | null;
  createdAt: string;
}

export interface JournalLineRecord {
  lineNo: number;
  accountType: string;
  side: string;
  currency: string;
  amount: string;
  fxRate: string;
  baseAmount: string;
  fxDirection: string;
  billTitle: string | null;
  billTypeCode: string | null;
}

export interface JournalRecord {
  id: string;
  status: JournalStatus | string;
  entryType: JournalEntryType | string;
  sourceType: string;
  sourceId: string;
  idempotencyKey: string;
  postedAt: string;
  lines: JournalLineRecord[];
}

export interface FxRateRecord {
  id: string;
  fromCurrency: string;
  toCurrency: string;
  rate: string;
  effectiveAt: string;
  enteredByUserId: string;
}

export interface QuarantineRecord {
  id: string;
  sourceTable: string;
  sourcePk: string;
  sourceHash: string | null;
  migrationRunId: string;
  reason: string;
  payload: unknown;
  reconciliationImpact: string | null;
  status: string;
  createdAt: string;
}

export interface ReconstructionResult {
  reconstructed: number;
  quarantined: number;
  journals: string[];
  requestsImported: number;
}

export interface ReconciliationGate {
  gate: string;
  passed: boolean;
  detail: string;
}

export interface ReconciliationResult {
  id: string;
  migrationRunId: string;
  status: string;
  gates: ReconciliationGate[];
  passed: boolean;
}
