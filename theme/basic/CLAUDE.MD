# Project Rules – STEP 3-1 (Width-based Pricing)

This project is a quotation system for sign printing.
Implement STEP 3-1 ONLY. Do not modify other logic.

## Goal
Automatically determine width category (단폭/장폭/초장폭) based on input size,
and apply the correct price per m².

## Width Determination Rule (CRITICAL)
- width_mm = min(input_width_mm, input_height_mm)
  (The smaller side is the material roll width)

### Categories
- width_mm ≤ 2100 → 단폭
- 2100 < width_mm ≤ 3100 → 장폭
- 3100 < width_mm ≤ 4800 → 초장폭
- width_mm > 4800 → 제작 불가 (show warning)

### Examples
- 5000 × 2000 → width_mm=2000 → 단폭
- 5000 × 2500 → width_mm=2500 → 장폭
- 10000 × 2000 → width_mm=2000 → 단폭

## Implementation Scope
- File to modify:
  - theme/basic/admin_quote_calc.php
- Optional:
  - product pricing rule structure if needed

## UI Requirement
Display the applied width category to the user:
Example:
"폭 2000mm → 단폭 단가 적용"

## Do NOT
- Do not hardcode prices yet
- Do not modify per_m2 or quantity option logic
- Do not touch unrelated files
