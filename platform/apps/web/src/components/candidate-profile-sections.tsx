'use client';

import { useCallback, useEffect, useState } from 'react';
import type {
  CandidateEducationRecord,
  CandidateExperienceRecord,
  CandidateLanguageRecord,
  CandidateSkillRecord,
  CandidateSkillTagRecord,
  CandidateTrainingRecord,
  ProfileListResult,
} from '@manpower/shared';
import { apiClient, ApiClientError } from '@/lib/api-client';

type Tab = 'education' | 'experience' | 'skills' | 'skill-tags' | 'languages' | 'training';

interface Props {
  candidateId: string;
  onForbidden: () => void;
}

export function CandidateProfileSections({ candidateId, onForbidden }: Props) {
  const [tab, setTab] = useState<Tab>('education');
  const [error, setError] = useState('');

  return (
    <section className="profile-panel">
      <h2>Profile records</h2>
      <div className="tab-row">
        {(
          [
            ['education', 'Education'],
            ['experience', 'Experience'],
            ['skills', 'Skills'],
            ['skill-tags', 'Skill tags'],
            ['languages', 'Languages'],
            ['training', 'Training'],
          ] as const
        ).map(([key, label]) => (
          <button
            key={key}
            type="button"
            className={tab === key ? 'tab active' : 'tab'}
            onClick={() => setTab(key)}
          >
            {label}
          </button>
        ))}
      </div>
      {error ? <p className="auth-error">{error}</p> : null}
      {tab === 'education' ? (
        <EducationPanel candidateId={candidateId} onError={setError} onForbidden={onForbidden} />
      ) : null}
      {tab === 'experience' ? (
        <ExperiencePanel candidateId={candidateId} onError={setError} onForbidden={onForbidden} />
      ) : null}
      {tab === 'skills' ? (
        <SkillPanel candidateId={candidateId} onError={setError} onForbidden={onForbidden} />
      ) : null}
      {tab === 'skill-tags' ? (
        <SkillTagPanel candidateId={candidateId} onError={setError} onForbidden={onForbidden} />
      ) : null}
      {tab === 'languages' ? (
        <LanguagePanel candidateId={candidateId} onError={setError} onForbidden={onForbidden} />
      ) : null}
      {tab === 'training' ? (
        <TrainingPanel candidateId={candidateId} onError={setError} onForbidden={onForbidden} />
      ) : null}
    </section>
  );
}

function handleError(caught: unknown, onError: (message: string) => void, onForbidden: () => void): void {
  if (caught instanceof ApiClientError && caught.statusCode === 403) {
    onForbidden();
    return;
  }
  onError(caught instanceof ApiClientError ? caught.message : 'Unable to save profile record');
}

function EducationPanel({
  candidateId,
  onError,
  onForbidden,
}: {
  candidateId: string;
  onError: (message: string) => void;
  onForbidden: () => void;
}) {
  const [items, setItems] = useState<CandidateEducationRecord[]>([]);
  const [editing, setEditing] = useState<CandidateEducationRecord | null>(null);
  const load = useCallback(() => {
    apiClient
      .get<ProfileListResult<CandidateEducationRecord>>(`/api/v1/candidates/${candidateId}/educations?limit=50`)
      .then((data) => setItems(data.items))
      .catch((caught) => handleError(caught, onError, onForbidden));
  }, [candidateId, onError, onForbidden]);
  useEffect(() => {
    load();
  }, [load]);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload = {
      examName: String(form.get('examName') ?? ''),
      instituteName: String(form.get('instituteName') ?? ''),
      subjectGroupMajor: String(form.get('subjectGroupMajor') ?? ''),
      boardName: String(form.get('boardName') ?? ''),
      result: String(form.get('result') ?? ''),
      passingYear: String(form.get('passingYear') ?? ''),
    };
    try {
      if (editing) {
        await apiClient.patch(`/api/v1/candidates/${candidateId}/educations/${editing.id}`, payload);
      } else {
        await apiClient.post(`/api/v1/candidates/${candidateId}/educations`, payload);
      }
      setEditing(null);
      event.currentTarget.reset();
      load();
    } catch (caught) {
      handleError(caught, onError, onForbidden);
    }
  }

  return (
    <div>
      <table className="data-table">
        <thead>
          <tr>
            <th>Exam</th>
            <th>Institute</th>
            <th>Result</th>
            <th>Year</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {items.map((item) => (
            <tr key={item.id}>
              <td>{item.examName ?? '—'}</td>
              <td>{item.instituteName ?? '—'}</td>
              <td>{item.result ?? '—'}</td>
              <td>{item.passingYear ?? '—'}</td>
              <td>
                <button type="button" onClick={() => setEditing(item)}>
                  Edit
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <form className="candidate-form" onSubmit={submit}>
        <h3>{editing ? 'Edit education' : 'Add education'}</h3>
        <label>
          Exam name
          <input name="examName" defaultValue={editing?.examName ?? ''} required key={`${editing?.id ?? 'new'}-exam`} />
        </label>
        <label>
          Institute
          <input name="instituteName" defaultValue={editing?.instituteName ?? ''} key={`${editing?.id ?? 'new'}-inst`} />
        </label>
        <label>
          Subject / major
          <input
            name="subjectGroupMajor"
            defaultValue={editing?.subjectGroupMajor ?? ''}
            key={`${editing?.id ?? 'new'}-major`}
          />
        </label>
        <label>
          Board
          <input name="boardName" defaultValue={editing?.boardName ?? ''} key={`${editing?.id ?? 'new'}-board`} />
        </label>
        <label>
          Result
          <input name="result" defaultValue={editing?.result ?? ''} key={`${editing?.id ?? 'new'}-result`} />
        </label>
        <label>
          Passing year
          <input name="passingYear" defaultValue={editing?.passingYear ?? ''} key={`${editing?.id ?? 'new'}-year`} />
        </label>
        <button type="submit">{editing ? 'Save education' : 'Add education'}</button>
      </form>
    </div>
  );
}

function ExperiencePanel({
  candidateId,
  onError,
  onForbidden,
}: {
  candidateId: string;
  onError: (message: string) => void;
  onForbidden: () => void;
}) {
  const [items, setItems] = useState<CandidateExperienceRecord[]>([]);
  const [editing, setEditing] = useState<CandidateExperienceRecord | null>(null);
  const load = useCallback(() => {
    apiClient
      .get<ProfileListResult<CandidateExperienceRecord>>(`/api/v1/candidates/${candidateId}/experiences?limit=50`)
      .then((data) => setItems(data.items))
      .catch((caught) => handleError(caught, onError, onForbidden));
  }, [candidateId, onError, onForbidden]);
  useEffect(() => {
    load();
  }, [load]);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload = {
      companyName: String(form.get('companyName') ?? ''),
      designation: String(form.get('designation') ?? ''),
      companyAddress: String(form.get('companyAddress') ?? ''),
      startDate: String(form.get('startDate') ?? ''),
      endDate: String(form.get('endDate') ?? ''),
      responsibilities: String(form.get('responsibilities') ?? ''),
    };
    try {
      if (editing) {
        await apiClient.patch(`/api/v1/candidates/${candidateId}/experiences/${editing.id}`, payload);
      } else {
        await apiClient.post(`/api/v1/candidates/${candidateId}/experiences`, payload);
      }
      setEditing(null);
      event.currentTarget.reset();
      load();
    } catch (caught) {
      handleError(caught, onError, onForbidden);
    }
  }

  return (
    <div>
      <table className="data-table">
        <thead>
          <tr>
            <th>Company</th>
            <th>Designation</th>
            <th>Start</th>
            <th>End</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {items.map((item) => (
            <tr key={item.id}>
              <td>{item.companyName ?? '—'}</td>
              <td>{item.designation ?? '—'}</td>
              <td>{item.startDate ?? '—'}</td>
              <td>{item.endDate ?? '—'}</td>
              <td>
                <button type="button" onClick={() => setEditing(item)}>
                  Edit
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <form className="candidate-form" onSubmit={submit}>
        <h3>{editing ? 'Edit experience' : 'Add experience'}</h3>
        <label>
          Company
          <input name="companyName" defaultValue={editing?.companyName ?? ''} required key={`${editing?.id ?? 'new'}-co`} />
        </label>
        <label>
          Designation
          <input name="designation" defaultValue={editing?.designation ?? ''} key={`${editing?.id ?? 'new'}-des`} />
        </label>
        <label>
          Address
          <input name="companyAddress" defaultValue={editing?.companyAddress ?? ''} key={`${editing?.id ?? 'new'}-addr`} />
        </label>
        <label>
          Start date
          <input name="startDate" type="date" defaultValue={editing?.startDate ?? ''} key={`${editing?.id ?? 'new'}-s`} />
        </label>
        <label>
          End date
          <input name="endDate" type="date" defaultValue={editing?.endDate ?? ''} key={`${editing?.id ?? 'new'}-e`} />
        </label>
        <label>
          Responsibilities
          <input
            name="responsibilities"
            defaultValue={editing?.responsibilities ?? ''}
            key={`${editing?.id ?? 'new'}-r`}
          />
        </label>
        <button type="submit">{editing ? 'Save experience' : 'Add experience'}</button>
      </form>
    </div>
  );
}

function SkillPanel({
  candidateId,
  onError,
  onForbidden,
}: {
  candidateId: string;
  onError: (message: string) => void;
  onForbidden: () => void;
}) {
  const [items, setItems] = useState<CandidateSkillRecord[]>([]);
  const [editing, setEditing] = useState<CandidateSkillRecord | null>(null);
  const load = useCallback(() => {
    apiClient
      .get<ProfileListResult<CandidateSkillRecord>>(`/api/v1/candidates/${candidateId}/skills?limit=50`)
      .then((data) => setItems(data.items))
      .catch((caught) => handleError(caught, onError, onForbidden));
  }, [candidateId, onError, onForbidden]);
  useEffect(() => {
    load();
  }, [load]);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload = {
      title: String(form.get('title') ?? ''),
      instituteName: String(form.get('instituteName') ?? ''),
      resultScore: String(form.get('resultScore') ?? ''),
      examScore: String(form.get('examScore') ?? ''),
      details: String(form.get('details') ?? ''),
    };
    try {
      if (editing) {
        await apiClient.patch(`/api/v1/candidates/${candidateId}/skills/${editing.id}`, payload);
      } else {
        await apiClient.post(`/api/v1/candidates/${candidateId}/skills`, payload);
      }
      setEditing(null);
      event.currentTarget.reset();
      load();
    } catch (caught) {
      handleError(caught, onError, onForbidden);
    }
  }

  return (
    <div>
      <table className="data-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Institute</th>
            <th>Score</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {items.map((item) => (
            <tr key={item.id}>
              <td>{item.title ?? '—'}</td>
              <td>{item.instituteName ?? '—'}</td>
              <td>{item.resultScore ?? '—'}</td>
              <td>
                <button type="button" onClick={() => setEditing(item)}>
                  Edit
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <form className="candidate-form" onSubmit={submit}>
        <h3>{editing ? 'Edit skill' : 'Add skill'}</h3>
        <label>
          Title
          <input name="title" defaultValue={editing?.title ?? ''} required key={`${editing?.id ?? 'new'}-t`} />
        </label>
        <label>
          Institute
          <input name="instituteName" defaultValue={editing?.instituteName ?? ''} key={`${editing?.id ?? 'new'}-i`} />
        </label>
        <label>
          Result score
          <input name="resultScore" defaultValue={editing?.resultScore ?? ''} key={`${editing?.id ?? 'new'}-rs`} />
        </label>
        <label>
          Exam score
          <input name="examScore" defaultValue={editing?.examScore ?? ''} key={`${editing?.id ?? 'new'}-es`} />
        </label>
        <label>
          Details
          <input name="details" defaultValue={editing?.details ?? ''} key={`${editing?.id ?? 'new'}-d`} />
        </label>
        <button type="submit">{editing ? 'Save skill' : 'Add skill'}</button>
      </form>
    </div>
  );
}

function SkillTagPanel({
  candidateId,
  onError,
  onForbidden,
}: {
  candidateId: string;
  onError: (message: string) => void;
  onForbidden: () => void;
}) {
  const [items, setItems] = useState<CandidateSkillTagRecord[]>([]);
  const load = useCallback(() => {
    apiClient
      .get<ProfileListResult<CandidateSkillTagRecord>>(`/api/v1/candidates/${candidateId}/skill-list?limit=50`)
      .then((data) => setItems(data.items))
      .catch((caught) => handleError(caught, onError, onForbidden));
  }, [candidateId, onError, onForbidden]);
  useEffect(() => {
    load();
  }, [load]);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    try {
      await apiClient.post(`/api/v1/candidates/${candidateId}/skill-list`, {
        skillName: String(form.get('skillName') ?? ''),
      });
      event.currentTarget.reset();
      load();
    } catch (caught) {
      handleError(caught, onError, onForbidden);
    }
  }

  return (
    <div>
      <ul className="plain-list">
        {items.map((item) => (
          <li key={item.id}>{item.skillName ?? '—'}</li>
        ))}
      </ul>
      <form className="candidate-form" onSubmit={submit}>
        <label>
          Skill name
          <input name="skillName" required />
        </label>
        <button type="submit">Add skill tag</button>
      </form>
    </div>
  );
}

function LanguagePanel({
  candidateId,
  onError,
  onForbidden,
}: {
  candidateId: string;
  onError: (message: string) => void;
  onForbidden: () => void;
}) {
  const [items, setItems] = useState<CandidateLanguageRecord[]>([]);
  const [editing, setEditing] = useState<CandidateLanguageRecord | null>(null);
  const load = useCallback(() => {
    apiClient
      .get<ProfileListResult<CandidateLanguageRecord>>(`/api/v1/candidates/${candidateId}/languages?limit=50`)
      .then((data) => setItems(data.items))
      .catch((caught) => handleError(caught, onError, onForbidden));
  }, [candidateId, onError, onForbidden]);
  useEffect(() => {
    load();
  }, [load]);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload = {
      languageName: String(form.get('languageName') ?? ''),
      languageStatus: String(form.get('languageStatus') ?? ''),
    };
    try {
      if (editing) {
        await apiClient.patch(`/api/v1/candidates/${candidateId}/languages/${editing.id}`, payload);
      } else {
        await apiClient.post(`/api/v1/candidates/${candidateId}/languages`, payload);
      }
      setEditing(null);
      event.currentTarget.reset();
      load();
    } catch (caught) {
      handleError(caught, onError, onForbidden);
    }
  }

  return (
    <div>
      <table className="data-table">
        <thead>
          <tr>
            <th>Language</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {items.map((item) => (
            <tr key={item.id}>
              <td>{item.languageName ?? '—'}</td>
              <td>{item.languageStatus ?? '—'}</td>
              <td>
                <button type="button" onClick={() => setEditing(item)}>
                  Edit
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <form className="candidate-form" onSubmit={submit}>
        <h3>{editing ? 'Edit language' : 'Add language'}</h3>
        <label>
          Language
          <input
            name="languageName"
            defaultValue={editing?.languageName ?? ''}
            required
            key={`${editing?.id ?? 'new'}-ln`}
          />
        </label>
        <label>
          Proficiency (free text)
          <input
            name="languageStatus"
            defaultValue={editing?.languageStatus ?? ''}
            key={`${editing?.id ?? 'new'}-ls`}
          />
        </label>
        <button type="submit">{editing ? 'Save language' : 'Add language'}</button>
      </form>
    </div>
  );
}

function TrainingPanel({
  candidateId,
  onError,
  onForbidden,
}: {
  candidateId: string;
  onError: (message: string) => void;
  onForbidden: () => void;
}) {
  const [items, setItems] = useState<CandidateTrainingRecord[]>([]);
  const [editing, setEditing] = useState<CandidateTrainingRecord | null>(null);
  const load = useCallback(() => {
    apiClient
      .get<ProfileListResult<CandidateTrainingRecord>>(`/api/v1/candidates/${candidateId}/trainings?limit=50`)
      .then((data) => setItems(data.items))
      .catch((caught) => handleError(caught, onError, onForbidden));
  }, [candidateId, onError, onForbidden]);
  useEffect(() => {
    load();
  }, [load]);

  async function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload = {
      title: String(form.get('title') ?? ''),
      instituteName: String(form.get('instituteName') ?? ''),
      address: String(form.get('address') ?? ''),
      countryRef: String(form.get('countryRef') ?? ''),
      startDate: String(form.get('startDate') ?? ''),
      endDate: String(form.get('endDate') ?? ''),
      topics: String(form.get('topics') ?? ''),
    };
    try {
      if (editing) {
        await apiClient.patch(`/api/v1/candidates/${candidateId}/trainings/${editing.id}`, payload);
      } else {
        await apiClient.post(`/api/v1/candidates/${candidateId}/trainings`, payload);
      }
      setEditing(null);
      event.currentTarget.reset();
      load();
    } catch (caught) {
      handleError(caught, onError, onForbidden);
    }
  }

  return (
    <div>
      <table className="data-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Institute</th>
            <th>Dates</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {items.map((item) => (
            <tr key={item.id}>
              <td>{item.title ?? '—'}</td>
              <td>{item.instituteName ?? '—'}</td>
              <td>
                {item.startDate ?? '—'} – {item.endDate ?? '—'}
              </td>
              <td>
                <button type="button" onClick={() => setEditing(item)}>
                  Edit
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <form className="candidate-form" onSubmit={submit}>
        <h3>{editing ? 'Edit training' : 'Add training'}</h3>
        <label>
          Title
          <input name="title" defaultValue={editing?.title ?? ''} required key={`${editing?.id ?? 'new'}-tt`} />
        </label>
        <label>
          Institute
          <input name="instituteName" defaultValue={editing?.instituteName ?? ''} key={`${editing?.id ?? 'new'}-ti`} />
        </label>
        <label>
          Address
          <input name="address" defaultValue={editing?.address ?? ''} key={`${editing?.id ?? 'new'}-ta`} />
        </label>
        <label>
          Country
          <input name="countryRef" defaultValue={editing?.countryRef ?? ''} key={`${editing?.id ?? 'new'}-tc`} />
        </label>
        <label>
          Start date
          <input name="startDate" type="date" defaultValue={editing?.startDate ?? ''} key={`${editing?.id ?? 'new'}-ts`} />
        </label>
        <label>
          End date
          <input name="endDate" type="date" defaultValue={editing?.endDate ?? ''} key={`${editing?.id ?? 'new'}-te`} />
        </label>
        <label>
          Topics
          <input name="topics" defaultValue={editing?.topics ?? ''} key={`${editing?.id ?? 'new'}-tp`} />
        </label>
        <button type="submit">{editing ? 'Save training' : 'Add training'}</button>
      </form>
    </div>
  );
}
