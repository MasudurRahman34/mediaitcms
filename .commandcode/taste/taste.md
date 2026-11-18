# Taste

## Tooling & environment

- Works in a Windows/Laragon environment and wants the agent to use the already-installed Laragon stack (its PHP, Composer and MySQL binaries/services) instead of installing or using global/standalone toolchains. Confidence: 0.6

## Version control

- Expects work committed and pushed to git branches (their repo uses `main`, `dev`, `test`) with suitable, descriptive commit messages — not left as uncommitted local changes. Confidence: 0.55
- Expects proper repo hygiene files to be part of the setup: a real `.gitignore` plus the other relevant ignore/config files (e.g. `.prettierignore`, `.editorconfig`), not just application code. Confidence: 0.5

## Communication & workflow

- Prefers the agent to make engineering decisions autonomously and avoid unnecessary clarifying questions and lengthy explanations; at each milestone report only: features implemented, files created/modified, commands to run, test/build results, remaining issues. Confidence: 0.6
