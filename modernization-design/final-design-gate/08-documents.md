# 08 — Document migration specification

`document-fields.csv` inventories **73** file-bearing schema/BREAD fields. It covers scalar paths, Voyager JSON arrays (`download_link`/`original_name`), images, signatures, logos, certificates, and attachment-like fields without reproducing any file names or personal data.

Target files are private objects with UUID, opaque object key, original name, detected MIME, size, SHA-256, source table/id/column/ordinal, timestamps, classification, malware-scan status, and retention/legal-hold fields. Never trust extension or client MIME. Copy streams, compute source/target checksum, compare size, then activate metadata. Missing DB references become MISSING; storage-only objects become ORPHAN; malformed JSON becomes QUARANTINED. None are silently deleted. Authorization requires resource access plus document classification; downloads are audited and use short-lived URLs.

Physical storage roots, object existence, access inheritance exceptions, retention periods, and malware policy are UNVERIFIED because only SQL/source evidence was authorized.
