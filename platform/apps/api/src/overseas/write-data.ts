import { toBigInt } from '../training/util.js';
import { parseDateOnly, parseTimeOnly } from './serialize.js';

export function optionalFileId(value: unknown): string | undefined {
  if (value === undefined) return undefined;
  if (value === null || value === '') return undefined;
  return value as string;
}

export function datedWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.candidateId !== undefined ? { candidateId: input.candidateId as string } : {}),
    ...(input.issueDate !== undefined ? { issueDate: parseDateOnly(input.issueDate as string) } : {}),
    ...(input.expireDate !== undefined ? { expireDate: parseDateOnly(input.expireDate as string) } : {}),
    ...(input.status !== undefined ? { status: input.status as string } : {}),
  };
}

export function medicalWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...datedWriteData(input),
    ...(input.documentFileId !== undefined ? { documentFileId: optionalFileId(input.documentFileId) ?? null } : {}),
    ...(input.countryId !== undefined ? { countryId: toBigInt(input.countryId as string) } : {}),
  };
}

export function policeWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...datedWriteData(input),
    ...(input.thanaId !== undefined ? { thanaId: toBigInt(input.thanaId as string) } : {}),
    ...(input.countryId !== undefined ? { countryId: toBigInt(input.countryId as string) } : {}),
    ...(input.photoFileId !== undefined ? { photoFileId: optionalFileId(input.photoFileId) ?? null } : {}),
  };
}

export function arcWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...datedWriteData(input),
    ...(input.isLifetime !== undefined ? { isLifetime: input.isLifetime as string } : {}),
    ...(input.arcFileId !== undefined ? { arcFileId: optionalFileId(input.arcFileId) ?? null } : {}),
    ...(input.arcNumber !== undefined ? { arcNumber: input.arcNumber as string } : {}),
  };
}

export function labourWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...datedWriteData(input),
    ...(input.documentFileId !== undefined ? { documentFileId: optionalFileId(input.documentFileId) ?? null } : {}),
  };
}

export function visaWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...datedWriteData(input),
    ...(input.visaMpNo !== undefined ? { visaMpNo: input.visaMpNo as string } : {}),
    ...(input.documentFileId !== undefined ? { documentFileId: optionalFileId(input.documentFileId) ?? null } : {}),
  };
}

export function flightWriteData(input: Record<string, unknown>): Record<string, unknown> {
  return {
    ...(input.sourceLegacyId !== undefined ? { sourceLegacyId: toBigInt(input.sourceLegacyId as string) } : {}),
    ...(input.candidateId !== undefined ? { candidateId: input.candidateId as string } : {}),
    ...(input.airlineceName !== undefined ? { airlineceName: input.airlineceName as string } : {}),
    ...(input.flightDate !== undefined ? { flightDate: parseDateOnly(input.flightDate as string) } : {}),
    ...(input.flightTime !== undefined ? { flightTime: parseTimeOnly(input.flightTime as string) } : {}),
    ...(input.departureTime !== undefined ? { departureTime: parseTimeOnly(input.departureTime as string) } : {}),
    ...(input.arrivalTime !== undefined ? { arrivalTime: parseTimeOnly(input.arrivalTime as string) } : {}),
    ...(input.ticketFileId !== undefined ? { ticketFileId: optionalFileId(input.ticketFileId) ?? null } : {}),
    ...(input.arrivalSealPageFileId !== undefined
      ? { arrivalSealPageFileId: optionalFileId(input.arrivalSealPageFileId) ?? null }
      : {}),
  };
}
