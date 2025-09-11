📚 Workflow Engine Documentation

Technical deep‑dive for the Laravel 11 + Livewire Workflow Engine that powers referral processing in this repository.  This file is meant to live at the root of the project as README.md, so contributors understand the data model, Livewire controller flow, and how to extend the engine.

Table of Contents

High‑Level Lifecycle

Database Schema

Step‑Type Matrix

Form Field Cheat‑Sheet

Livewire Controller API

Validation & Error Surface

Concurrency Guarantees

Notification Dispatch

File Storage Layout

Security & RBAC

Seeder Authoring Checklist

Future Roadmap

Revision History

1 · High‑Level Lifecycle

sequenceDiagram
  participant UI as Browser (Livewire)
  participant LW as ReferralWorkflowShow.php
  participant DB as MySQL
  UI->>LW: mount(referralId)
  LW->>DB: eager‑load workflow, progress, files
  UI->>LW: save<Type>() action
  LW->>LW: validate + authorise
  LW->>DB: upsert referral_progress
  alt step needs files
    LW->>Storage: store files / signatures
  end
  LW->>Notifications: dispatch StepCompletedNotification
  LW->>DB: reload fresh relationships
  LW-->>UI: flash messages & hydrate

All save handlers are idempotent thanks to updateOrCreate on {referral_id, workflow_step_id}.

2 · Database Schema

Table

Key Columns

Purpose

workflows

id, name

Workflow definition seed.

workflow_stages

id, workflow_id, name, order

Ordered sections inside a workflow.

workflow_steps

id, workflow_stage_id, name, type, order, metadata JSON, group_can_write JSON, group_can_see JSON, group_get_notif JSON

UI source‑of‑truth. metadata ≤ 64 KB.

referrals

id, …

Business objects moving through a workflow.

referral_progress

id, referral_id, workflow_step_id, status enum(pending,completed,skipped), completed_by → users.id, completed_at, notes LONGTEXT

History of completions. Latest row = current state.

uploaded_files

id, referral_id, referral_progress_id, original_name, path

Binary decoupling for uploads.

step_comments

id, workflow_step_id, referral_id, user_id, comment, created_at

Discussion thread per step/referral.

Indexes — (referral_id, workflow_step_id, completed_at) on referral_progress ; path is UNIQUE in uploaded_files.

3 · Step‑Type Matrix

Legend — RP: Reactive Property  ·  Save: Handler method  ·  Blade: Component in resources/views/livewire/steps/

Type

Blade

RP Binding

Save Method

Required metadata

Progress notes Payload

form

form-step.blade.php

formAnswers[stepId]

saveForm()

fields (array) · optional pdf_template

JSON keyed by field name; signatures → signatures/*.png.

decision

decision-step.blade.php

decisionAnswers[stepId]

saveDecision()

question, options

Plain string (answer).

checkbox

checkbox-step.blade.php

checkboxAnswers[stepId]

saveCheckbox()

label

"Checkbox marked done".

upload

upload-step.blade.php

uploadFiles[stepId][]

saveUpload()

upload_label, allowed_mimes, max_files, max_size

"Uploaded {n} file(s).". Individual paths in uploaded_files.

notify

notify-step.blade.php

notifyData[stepId]

sendFamilyNotification()

label

JSON {family_name, family_email, custom_note}.

med_rec

med_rec-step.blade.php

finalMeds, facilityList, epicList

saveMedRec()

—

JSON {final_meds, facility_list, epic_list}.

action (TBD)

action-step.blade.php

actionStatus[stepId]

saveAction()

label, due_in_days

JSON {status, completed_note}.

3.1 form Field Cheat‑Sheet

Field type

Widget

Validation Stub

text

<input type="text">

`string

max:255`

textarea

<textarea>

string

number

<input type="number">

numeric

date

<input type="date">

date

select

<select>

in:…options[]

multiselect

<select multiple>

`array

min:1`

checkbox

<input type="checkbox">

boolean

signature

<canvas> or file input

`string

regex:/signatures/.*.(png

jpg)$/`

4 · Livewire Controller API

File: app/Livewire/ReferralWorkflowShow.php

// 👉 life‑cycle
mount($referralId)
render()

// 👉 permission helper
private userCanWriteStep(int $stepId)

// 👉 decision
saveDecision(int $stepId)

// 👉 checkbox
saveCheckbox(int $stepId)

// 👉 upload
saveUpload(int $stepId)

// 👉 form
editForm(int $stepId)
saveForm(int $stepId)

// 👉 med‑rec
editMedRec(int $stepId)
saveMedRec(int $stepId)

// 👉 notify
sendFamilyNotification(int $stepId)

// 👉 comments
toggleComments(int $stepId)
addComment(int $stepId)

4.1 Validation & Error Surface

All save* methods flash session('error') or session('success'). The parent layout displays them via Tailwind alerts.

4.2 Concurrency Guarantees

updateOrCreate keeps handlers idempotent.

Livewire’s temp‑file IDs + storeAs() avoid filename collisions.

4.3 Notification Dispatch

$users = User::all()->filter(fn ($u) =>
    array_intersect($u->group, $step->group_get_notif)
);
foreach ($users as $u) {
    $u->notify(new StepCompletedNotification(referralId: …));
}

Queued; no blocking I/O during request.

5 · File Storage Layout

public/
├─ uploads/         # user documents (PDF, JPG, …)
├─ signatures/      # base‑64 captured PNG/JPG esignatures
└─ tmp/             # Livewire temporary files (auto‑pruned)

UploadedFile::path stores relative paths. Use Storage::disk('public')->url($path) for signed links.

6 · Security & RBAC

Write Guard — intersection of Auth::user()->group & group_can_write.

View Guard — per‑component check against group_can_see.

File Validation — MIME sniff + size limit on back‑end.

XSS — Never render notes raw; always {{ }} escape.

7 · Seeder Authoring Checklist



8 · Future Roadmap

Feature

Status

Planned File

Notes

action step type

Spec drafted

action-step.blade.php

Status dropdown + SLA reminders.

WebSocket sync

TODO

—

Use pusher driver for real‑time collab.

Role editor GUI

TODO

—

Admin UI for group_can_* arrays.