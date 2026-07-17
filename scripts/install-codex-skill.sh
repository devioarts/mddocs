#!/usr/bin/env bash
set -euo pipefail

REPO_URL="${MDDOCS_REPO_URL:-https://github.com/devioarts/mddocs.git}"
CODEX_HOME_DIR="${CODEX_HOME:-$HOME/.codex}"
DEST_DIR="${MDDOCS_CODEX_SKILLS_DIR:-$CODEX_HOME_DIR/skills}"

tmpdir=""

cleanup() {
    if [[ -n "$tmpdir" && -d "$tmpdir" ]]; then
        rm -rf "$tmpdir"
    fi
}

trap cleanup EXIT

run_helper() {
    local helper="$1"

    if [[ ! -f "$helper" ]]; then
        return 1
    fi

    bash "$helper" "Codex" "$DEST_DIR"
}

if [[ -n "${BASH_SOURCE[0]:-}" && -f "${BASH_SOURCE[0]}" ]]; then
    SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"

    if run_helper "$SCRIPT_DIR/lib/install-skill.sh"; then
        exit 0
    fi
fi

if ! command -v git >/dev/null 2>&1; then
    echo "git is required to install MDDocs skill from GitHub." >&2
    exit 1
fi

tmpdir="$(mktemp -d)"
git clone --depth 1 "$REPO_URL" "$tmpdir/mddocs" >/dev/null
run_helper "$tmpdir/mddocs/scripts/lib/install-skill.sh"
