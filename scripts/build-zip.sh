#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
PLUGIN_SLUG="listeo-bulk-add-listings"
PLUGIN_SRC="${REPO_ROOT}/plugin/${PLUGIN_SLUG}"
DIST_DIR="${REPO_ROOT}/dist"
OUTPUT_ZIP="${DIST_DIR}/${PLUGIN_SLUG}.zip"

if [ ! -f "${PLUGIN_SRC}/${PLUGIN_SLUG}.php" ]; then
  echo "Error: Main plugin file not found at ${PLUGIN_SRC}/${PLUGIN_SLUG}.php" >&2
  exit 1
fi

mkdir -p "${DIST_DIR}"
rm -f "${OUTPUT_ZIP}"

python3 - "${PLUGIN_SRC}" "${OUTPUT_ZIP}" "${PLUGIN_SLUG}" <<'PY'
import os
import sys
import zipfile

src_dir, out_zip, slug = sys.argv[1], sys.argv[2], sys.argv[3]
excluded = {".DS_Store", "Thumbs.db"}

with zipfile.ZipFile(out_zip, "w", zipfile.ZIP_DEFLATED) as zf:
    for root, dirs, files in os.walk(src_dir):
        dirs[:] = sorted(d for d in dirs if not d.startswith("."))
        for fname in sorted(files):
            if fname in excluded or fname.startswith("."):
                continue
            full_path = os.path.join(root, fname)
            rel_path = os.path.relpath(full_path, src_dir)
            arcname = os.path.join(slug, rel_path)
            zf.write(full_path, arcname)

print(f"Built {out_zip}")
PY
