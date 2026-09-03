import { describe, expect, it, vi } from 'vitest';
import { generateCandidateCode } from '../src/candidates/code';

describe('candidate code allocator', () => {
  it('formats nextval as 9 plus six digits and does not count rows', async () => {
    const tx = {
      $queryRaw: vi.fn().mockResolvedValue([{ next: 12n }]),
      candidate: { count: vi.fn() },
    };
    const code = await generateCandidateCode(tx as never);
    expect(code).toBe('9000012');
    expect(tx.$queryRaw).toHaveBeenCalledOnce();
    expect(tx.candidate.count).not.toHaveBeenCalled();
  });
});
