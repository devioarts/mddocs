#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 2 ]]; then
    echo "Usage: install-skill.sh <agent-name> <destination-dir>" >&2
    exit 1
fi

AGENT_NAME="$1"
DEST_DIR="$2"
REPO_URL="${MDDOCS_REPO_URL:-https://github.com/devioarts/mddocs.git}"
SKILL_NAME="mddocs"
DEST="$DEST_DIR/$SKILL_NAME"

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
LOCAL_REPO="$(cd -- "$SCRIPT_DIR/../.." >/dev/null 2>&1 && pwd)"
LOCAL_SKILL="$LOCAL_REPO/skills/$SKILL_NAME"

tmpdir=""

cleanup() {
    if [[ -n "$tmpdir" && -d "$tmpdir" ]]; then
        rm -rf "$tmpdir"
    fi
}

trap cleanup EXIT

if [[ -d "$LOCAL_SKILL" && -f "$LOCAL_SKILL/SKILL.md" ]]; then
    SOURCE="$LOCAL_SKILL"
else
    if ! command -v git >/dev/null 2>&1; then
        echo "git is required when the script is not run from a MDDocs checkout." >&2
        exit 1
    fi

    tmpdir="$(mktemp -d)"
    git clone --depth 1 "$REPO_URL" "$tmpdir/mddocs" >/dev/null
    SOURCE="$tmpdir/mddocs/skills/$SKILL_NAME"
fi

if [[ ! -f "$SOURCE/SKILL.md" ]]; then
    echo "MDDocs skill was not found at: $SOURCE" >&2
    exit 1
fi

mkdir -p "$DEST_DIR"
rm -rf "$DEST"
cp -R "$SOURCE" "$DEST"

echo "Installed MDDocs $AGENT_NAME skill to:"
echo "$DEST"
