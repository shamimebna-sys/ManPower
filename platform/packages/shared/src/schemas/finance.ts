import { z } from 'zod';

export const MoneyStringSchema = z
  .string()
  .regex(/^-?\d+(\.\d+)?$/, 'Amount must be a decimal string');

export const PaymentRequestWriteSchema = z.object({
  candidateId: z.string().uuid(),
  walletId: z.string().uuid().optional(),
  amount: MoneyStringSchema,
  billTitle: z.string().min(1).max(120),
  legacyBillCode: z.number().int().optional(),
});

export const PaymentRequestIdParams = z.object({ id: z.string().uuid() });

export const WalletDepositWriteSchema = z.object({
  walletId: z.string().uuid(),
  amount: MoneyStringSchema,
  currencyCode: z.enum(['BDT', 'EUR']),
  fxRateEntryId: z.string().uuid().optional(),
});

export const FxRateWriteSchema = z.object({
  fromCurrency: z.enum(['BDT', 'EUR']),
  toCurrency: z.enum(['BDT', 'EUR']),
  rate: MoneyStringSchema,
  effectiveAt: z.coerce.date().optional(),
});

export const LegacyPaymentSchema = z.object({
  id: z.string().min(1),
  status: z.string(),
  type: z.string(),
  amount: z.string(),
  currencyId: z.number().int(),
  exchangeRate: z.string(),
  agentId: z.number().int().nullable(),
  subAgentId: z.number().int().nullable(),
  teacherId: z.number().int().nullable(),
  sourceTable: z.string().optional(),
  sourceHash: z.string().min(1),
  isBackup: z.boolean().optional(),
});

export const LegacyPaymentRequestSchema = z.object({
  id: z.string().min(1),
  status: z.string(),
  amount: z.string(),
  billTitle: z.string().min(1).max(120),
  legacyBillCode: z.number().int().optional(),
  candidateId: z.string().uuid().optional(),
  agentId: z.number().int().nullable().optional(),
  subAgentId: z.number().int().nullable().optional(),
  paymentId: z.string().optional(),
  sourceTable: z.string().optional(),
  sourceHash: z.string().min(1),
  isBackup: z.boolean().optional(),
});

export const ReconstructionWriteSchema = z.object({
  migrationRunId: z.string().min(1).max(100),
  payments: z.array(LegacyPaymentSchema).default([]),
  requests: z.array(LegacyPaymentRequestSchema).default([]),
});

export const ReconciliationWriteSchema = z.object({
  migrationRunId: z.string().min(1).max(100),
});

export const JournalIdParams = z.object({ id: z.string().uuid() });
export const ReverseJournalSchema = z.object({ reason: z.string().min(1).max(200) });
export const WalletIdParams = z.object({ id: z.string().uuid() });
export const FinanceListQuerySchema = z.object({
  cursor: z.string().uuid().optional(),
  limit: z.coerce.number().int().min(1).max(100).default(20),
  status: z.string().optional(),
  walletId: z.string().uuid().optional(),
});
