@extends('layouts.app')
@section('content')
<style>
*{box-sizing:border-box}
.fx-wrap{margin:-24px -24px 0;background:#e8eaf0;height:calc(100vh - 70px);display:flex;flex-direction:column;font-family:'Montserrat',sans-serif}
.fx-bar{background:#fff;border-bottom:1px solid #dde1e7;height:46px;display:flex;align-items:center;gap:8px;padding:0 14px;flex-shrink:0}
.fx-bar-title{font-weight:800;font-size:14px;color:#111;flex:1}
.fx-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:700;border:none;cursor:pointer;font-family:inherit;text-decoration:none;transition:all .15s}
.fx-btn-teal{background:#0f766e;color:#fff}.fx-btn-teal:hover{background:#0b5f58}
.fx-btn-ghost{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb}.fx-btn-ghost:hover{background:#e5e7eb}
.fx-body{display:flex;flex:1;overflow:hidden}
/* Sidebar */
.fx-sidebar{width:220px;background:#fff;border-right:1px solid #dde1e7;display:flex;flex-direction:column;flex-shrink:0;overflow-y:auto}
.fx-sidebar-head{padding:10px 12px 6px;font-size:10px;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em}
.fx-folder-item{display:flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer;border-radius:0;transition:background .12s;position:relative;border:2px solid transparent;margin:1px 4px;border-radius:7px}
.fx-folder-item:hover{background:#f0fdf9}
.fx-folder-item.active{background:#ecfdf8;color:#0f766e;font-weight:700}
.fx-folder-item.drag-over{background:#ccfbf1!important;border-color:#0f9f8f!important;box-shadow:0 0 0 2px #0f9f8f40}
.fx-folder-icon{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.fx-folder-label{font-size:12px;font-weight:600;color:#374151;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fx-folder-item.active .fx-folder-label{color:#0f766e}
.fx-folder-count{font-size:10px;color:#9ca3af;background:#f3f4f6;padding:1px 6px;border-radius:99px;font-weight:700;flex-shrink:0}
.fx-divider{height:1px;background:#f3f4f6;margin:6px 0}
/* Main */
.fx-main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.fx-toolbar{background:#fff;border-bottom:1px solid #dde1e7;padding:8px 16px;display:flex;align-items:center;gap:10px;flex-shrink:0}
.fx-path{font-size:12px;color:#6b7280;flex:1}
.fx-path strong{color:#111;font-weight:700}
.fx-search{padding:6px 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12px;font-family:inherit;outline:none;width:180px}
.fx-search:focus{border-color:#0f9f8f}
.fx-content{flex:1;overflow-y:auto;padding:16px}
.fx-drop-zone{min-height:100%;border-radius:10px;border:2px dashed transparent;transition:all .2s;padding:4px}
.fx-drop-zone.drag-over-root{border-color:#0f9f8f;background:#ecfdf830}
/* Document icons */
.doc-grid{display:flex;flex-wrap:wrap;gap:12px;align-content:flex-start}
.doc-icon{width:110px;cursor:grab;user-select:none;border-radius:10px;padding:10px 8px 8px;text-align:center;border:2px solid transparent;transition:all .15s;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.07)}
.doc-icon:hover{box-shadow:0 4px 12px rgba(0,0,0,.12);transform:translateY(-2px)}
.doc-icon.dragging{opacity:.4;transform:scale(.95)}
.doc-icon.selected{border-color:#0f9f8f;background:#ecfdf8}
.doc-icon-img{width:54px;height:66px;margin:0 auto 6px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:22px;position:relative}
.doc-icon-name{font-size:10px;font-weight:700;color:#111;word-break:break-word;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.doc-icon-meta{font-size:9px;color:#9ca3af;margin-top:3px}
.doc-badge{display:inline-block;padding:1px 6px;border-radius:99px;font-size:9px;font-weight:800;margin-top:3px}
.doc-badge.active{background:#d1fae5;color:#065f46}
.doc-badge.archived{background:#f3f4f6;color:#374151}
.doc-badge.expired{background:#fee2e2;color:#991b1b}
/* Toast */
.fx-toast{position:fixed;bottom:24px;right:24px;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;z-index:9999;display:none;animation:toast-in .2s ease}
.fx-toast.success{background:#059669}.fx-toast.error{background:#dc2626}
@keyframes toast-in{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
/* Modals */
.fx-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center}
.fx-modal-bg.open{display:flex}
.fx-modal{background:#fff;border-radius:16px;padding:28px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.25)}
.fx-modal-title{font-size:17px;font-weight:800;color:#111;margin-bottom:18px}
.fm-group{margin-bottom:14px}
.fm-group label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px}
.fm-group input,.fm-group select,.fm-group textarea{width:100%;padding:9px 11px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;font-family:inherit;outline:none}
.fm-group input:focus,.fm-group select:focus,.fm-group textarea:focus{border-color:#0f9f8f}
.color-row{display:flex;gap:7px;flex-wrap:wrap;margin-top:4px}
.cswatch{width:26px;height:26px;border-radius:6px;cursor:pointer;border:2px solid transparent;transition:border-color .15s}
.cswatch.sel{border-color:#111}
.fm-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:20px}
.empty-state{text-align:center;padding:60px 20px;color:#9ca3af}
.empty-state p{font-size:15px;font-weight:700;color:#374151;margin-bottom:6px}
</style>

@php
$palette = ['#0f9f8f','#2563eb','#7c3aed','#be185d','#b45309','#15803d','#374151','#0369a1'];
$typeColors = ['contract'=>'#2563eb','invoice'=>'#f59e0b','report'=>'#10b981','other'=>'#9ca3af'];
$typeIcons  = ['contract'=>'📋','invoice'=>'🧾','report'=>'📊','other'=>'📄'];
@endphp

<div class="fx-wrap">
  {{-- Top bar --}}
  <div class="fx-bar">
    <span class="fx-bar-title">📁 Document Explorer</span>
    <a href="{{ route('documents.index') }}" class="fx-btn fx-btn-ghost">← Back</a>
    <a href="{{ route('documents.create') }}" class="fx-btn fx-btn-ghost">+ Add Document</a>
    <button class="fx-btn fx-btn-teal" onclick="openCreateModal()">+ New Folder</button>
  </div>

  <div class="fx-body">
    {{-- Sidebar --}}
    <div class="fx-sidebar" id="sidebar">
      <div class="fx-sidebar-head">Locations</div>

      {{-- Root (unassigned) --}}
      <div class="fx-folder-item active" data-folder-id="" id="sidebar-root"
           onclick="selectFolder('','All Documents')"
           ondragover="onDragOverFolder(event,this)"
           ondragleave="onDragLeaveFolder(this)"
           ondrop="onDropToFolder(event,'')">
        <div class="fx-folder-icon" style="background:#f3f4f6">
          <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </div>
        <span class="fx-folder-label">All Documents</span>
        <span class="fx-folder-count" id="count-root">{{ $documents->count() }}</span>
      </div>

      <div class="fx-divider"></div>
      <div class="fx-sidebar-head">Folders</div>

      @forelse($folders as $folder)
        <div class="fx-folder-item" data-folder-id="{{ $folder->id }}"
             id="sidebar-{{ $folder->id }}"
             onclick="selectFolder('{{ $folder->id }}','{{ addslashes($folder->name) }}')"
             ondragover="onDragOverFolder(event,this)"
             ondragleave="onDragLeaveFolder(this)"
             ondrop="onDropToFolder(event,'{{ $folder->id }}')"
             ondblclick="window.location='{{ route('document-folders.show', $folder) }}'">
          <div class="fx-folder-icon" style="background:{{ $folder->color }}22">
            <svg width="16" height="16" fill="none" stroke="{{ $folder->color }}" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>
          <span class="fx-folder-label" title="{{ $folder->name }}">{{ $folder->name }}</span>
          <span class="fx-folder-count" id="count-{{ $folder->id }}">{{ $folder->documents_count }}</span>
        </div>
      @empty
        <div style="padding:12px 14px;font-size:11px;color:#9ca3af">No folders yet. Create one →</div>
      @endforelse

      <div class="fx-divider"></div>
      {{-- Folder management buttons --}}
      @foreach($folders as $folder)
        <div style="display:flex;gap:4px;padding:3px 8px;align-items:center">
          <span style="font-size:11px;color:#374151;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $folder->name }}</span>
          <button onclick='openEditModal({{ json_encode(["id"=>$folder->id,"name"=>$folder->name,"description"=>$folder->description,"color"=>$folder->color,"project_id"=>$folder->project_id]) }})' style="border:none;background:none;cursor:pointer;font-size:10px;color:#6b7280;padding:2px 5px;border-radius:4px" title="Edit">✏️</button>
          <button onclick="deleteFolder({{ $folder->id }},'{{ addslashes($folder->name) }}',{{ $folder->documents_count }})" style="border:none;background:none;cursor:pointer;font-size:10px;color:#dc2626;padding:2px 5px;border-radius:4px" title="Delete">🗑</button>
        </div>
      @endforeach
    </div>

    {{-- Main content --}}
    <div class="fx-main">
      <div class="fx-toolbar">
        <div class="fx-path">📂 <strong id="current-path">All Documents</strong></div>
        <input class="fx-search" type="text" placeholder="Search documents…" oninput="filterDocs(this.value)" id="search-box">
        <span style="font-size:12px;color:#9ca3af" id="doc-count-label">{{ $documents->count() }} items</span>
      </div>

      <div class="fx-content">
        <div class="fx-drop-zone" id="root-drop-zone"
             ondragover="onDragOverRoot(event)"
             ondragleave="onDragLeaveRoot()"
             ondrop="onDropToFolder(event,'')">
          <div class="doc-grid" id="doc-grid">
            @forelse($documents as $doc)
              @php $ext = $doc->file_extension ?? ''; @endphp
              <div class="doc-icon"
                   id="doc-{{ $doc->id }}"
                   data-doc-id="{{ $doc->id }}"
                   data-folder-id="{{ $doc->folder_id ?? '' }}"
                   data-name="{{ strtolower($doc->title) }}"
                   draggable="true"
                   ondragstart="onDragStart(event,this)"
                   ondragend="onDragEnd(this)"
                   onclick="selectDoc(this)"
                   ondblclick="window.location='{{ route('documents.show', $doc) }}'">
                <div class="doc-icon-img" style="background:{{ ($typeColors[$doc->document_type] ?? '#9ca3af') }}18">
                  <span style="font-size:28px">{{ $typeIcons[$doc->document_type] ?? '📄' }}</span>
                  @if($ext)<span style="position:absolute;bottom:4px;right:6px;font-size:8px;font-weight:900;color:#fff;background:{{ $typeColors[$doc->document_type] ?? '#9ca3af' }};padding:1px 4px;border-radius:3px">{{ strtoupper($ext) }}</span>@endif
                </div>
                <div class="doc-icon-name" title="{{ $doc->title }}">{{ $doc->title }}</div>
                <div class="doc-icon-meta">{{ $doc->document_number }}</div>
                <span class="doc-badge {{ $doc->status }}">{{ ucfirst($doc->status) }}</span>
              </div>
            @empty
              <div class="empty-state" style="width:100%">
                <div style="font-size:48px;margin-bottom:12px">📭</div>
                <p>No documents yet</p>
                <small><a href="{{ route('documents.create') }}" style="color:#0f9f8f">Add your first document →</a></small>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Toast --}}
<div class="fx-toast" id="toast"></div>

{{-- CREATE MODAL --}}
<div class="fx-modal-bg" id="create-modal">
  <div class="fx-modal">
    <div class="fx-modal-title">📁 New Folder</div>
    <form method="POST" action="{{ route('document-folders.store') }}">
      @csrf
      <div class="fm-group"><label>Name *</label><input name="name" required placeholder="e.g. WAR Reports 2026" id="cn"></div>
      <div class="fm-group"><label>Description</label><textarea name="description" rows="2" placeholder="Optional…"></textarea></div>
      <div class="fm-group">
        <label>Color</label>
        <div class="color-row" id="cc-row">
          @foreach($palette as $c)<div class="cswatch {{ $c==='#0f9f8f'?'sel':'' }}" style="background:{{$c}}" data-color="{{$c}}" onclick="pickColor('c','{{$c}}')"></div>@endforeach
        </div>
        <input type="hidden" name="color" id="c-color" value="#0f9f8f">
      </div>
      <div class="fm-group"><label>Project (optional)</label><select name="project_id"><option value="">—</option>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
      <div class="fm-footer">
        <button type="button" class="fx-btn fx-btn-ghost" onclick="closeModal('create-modal')">Cancel</button>
        <button type="submit" class="fx-btn fx-btn-teal">Create</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div class="fx-modal-bg" id="edit-modal">
  <div class="fx-modal">
    <div class="fx-modal-title">✏️ Edit Folder</div>
    <form id="edit-form" method="POST">
      @csrf @method('PUT')
      <div class="fm-group"><label>Name *</label><input name="name" id="en" required></div>
      <div class="fm-group"><label>Description</label><textarea name="description" id="ed" rows="2"></textarea></div>
      <div class="fm-group">
        <label>Color</label>
        <div class="color-row" id="ec-row">
          @foreach($palette as $c)<div class="cswatch" style="background:{{$c}}" data-color="{{$c}}" onclick="pickColor('e','{{$c}}')"></div>@endforeach
        </div>
        <input type="hidden" name="color" id="e-color">
      </div>
      <div class="fm-group"><label>Project (optional)</label><select name="project_id" id="ep"><option value="">—</option>@foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
      <div class="fm-footer">
        <button type="button" class="fx-btn fx-btn-ghost" onclick="closeModal('edit-modal')">Cancel</button>
        <button type="submit" class="fx-btn fx-btn-teal">Save</button>
      </div>
    </form>
  </div>
</div>

{{-- DELETE MODAL --}}
<div class="fx-modal-bg" id="del-modal">
  <div class="fx-modal" style="text-align:center">
    <div style="width:52px;height:52px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px">🗑</div>
    <div class="fx-modal-title" style="margin-bottom:8px">Delete Folder?</div>
    <p id="del-msg" style="font-size:13px;color:#6b7280;margin-bottom:20px"></p>
    <form id="del-form" method="POST">
      @csrf @method('DELETE')
      <input type="hidden" name="force" value="1">
      <div style="display:flex;gap:10px;justify-content:center">
        <button type="button" class="fx-btn fx-btn-ghost" onclick="closeModal('del-modal')">Cancel</button>
        <button type="submit" class="fx-btn" style="background:#dc2626;color:#fff">Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let dragDocId = null;
let activeFolderId = '';

/* ── Drag source ─────────────────────────────────────────── */
function onDragStart(e, el){
  dragDocId = el.dataset.docId;
  el.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
  e.dataTransfer.setData('text/plain', dragDocId);
}
function onDragEnd(el){ el.classList.remove('dragging'); }

/* ── Folder drop targets ─────────────────────────────────── */
function onDragOverFolder(e, el){
  e.preventDefault(); e.dataTransfer.dropEffect='move';
  el.classList.add('drag-over');
}
function onDragLeaveFolder(el){ el.classList.remove('drag-over'); }

function onDragOverRoot(e){
  e.preventDefault();
  if(activeFolderId !== '') document.getElementById('root-drop-zone').classList.add('drag-over-root');
}
function onDragLeaveRoot(){ document.getElementById('root-drop-zone').classList.remove('drag-over-root'); }

function onDropToFolder(e, folderId){
  e.preventDefault();
  document.querySelectorAll('.fx-folder-item').forEach(x=>x.classList.remove('drag-over'));
  document.getElementById('root-drop-zone').classList.remove('drag-over-root');
  if(!dragDocId) return;

  const docEl = document.getElementById('doc-'+dragDocId);
  const prevFolder = docEl.dataset.folderId;
  if(prevFolder == folderId) return; // no change

  fetch(`/documents/${dragDocId}/set-folder`, {
    method:'PATCH',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
    body: JSON.stringify({folder_id: folderId || null})
  })
  .then(r=>r.json())
  .then(data=>{
    if(!data.success){ toast('Move failed','error'); return; }
    // Update doc data
    docEl.dataset.folderId = folderId;
    // Update sidebar counts
    updateCount(prevFolder, -1);
    updateCount(folderId, +1);
    // If viewing a specific folder, hide/show doc
    if(activeFolderId !== '' && folderId !== activeFolderId) docEl.style.display='none';
    else docEl.style.display='';
    const folderName = folderId
      ? (document.getElementById('sidebar-'+folderId)?.querySelector('.fx-folder-label')?.textContent || 'folder')
      : 'root';
    toast(`Moved to "${folderName}"`, 'success');
    updateDocLabel();
  })
  .catch(()=>toast('Error moving document','error'));

  dragDocId = null;
}

/* ── Sidebar folder selection ────────────────────────────── */
function selectFolder(folderId, name){
  activeFolderId = folderId;
  document.getElementById('current-path').textContent = name;
  document.querySelectorAll('.fx-folder-item').forEach(x=>x.classList.remove('active'));
  const sid = folderId ? 'sidebar-'+folderId : 'sidebar-root';
  document.getElementById(sid)?.classList.add('active');
  filterDocs(document.getElementById('search-box').value);
}

function filterDocs(search){
  let visible = 0;
  search = search.toLowerCase();
  document.querySelectorAll('.doc-icon').forEach(el=>{
    const folderMatch = activeFolderId==='' || el.dataset.folderId===activeFolderId;
    const nameMatch   = !search || el.dataset.name.includes(search);
    const show = folderMatch && nameMatch;
    el.style.display = show ? '' : 'none';
    if(show) visible++;
  });
  document.getElementById('doc-count-label').textContent = visible+' items';
}

function updateCount(folderId, delta){
  const id = folderId ? 'count-'+folderId : 'count-root';
  const el = document.getElementById(id);
  if(el) el.textContent = Math.max(0, parseInt(el.textContent||0)+delta);
}

function updateDocLabel(){
  let v=0;
  document.querySelectorAll('.doc-icon').forEach(el=>{ if(el.style.display!=='none') v++; });
  document.getElementById('doc-count-label').textContent = v+' items';
}

/* ── Selection ───────────────────────────────────────────── */
function selectDoc(el){
  document.querySelectorAll('.doc-icon.selected').forEach(x=>x.classList.remove('selected'));
  el.classList.add('selected');
}

/* ── Toast ───────────────────────────────────────────────── */
function toast(msg, type='success'){
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'fx-toast '+type; t.style.display='block';
  setTimeout(()=>{ t.style.display='none'; }, 2800);
}

/* ── Modals ──────────────────────────────────────────────── */
function openCreateModal(){ document.getElementById('create-modal').classList.add('open'); document.getElementById('cn').focus(); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.fx-modal-bg').forEach(m=>m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); }));

function pickColor(prefix, color){
  document.querySelectorAll('#'+prefix+'c-row .cswatch, #'+prefix+'C-row .cswatch').forEach(s=>s.classList.remove('sel'));
  // handle both id patterns
  ['#cc-row','#ec-row'].forEach(r=>{
    const row = document.querySelector(r);
    if(row) row.querySelectorAll('.cswatch').forEach(s=>s.classList.toggle('sel', s.dataset.color===color));
  });
  document.getElementById(prefix+'-color').value = color;
}

function openEditModal(f){
  document.getElementById('edit-form').action = '/document-folders/'+f.id;
  document.getElementById('en').value = f.name;
  document.getElementById('ed').value = f.description||'';
  document.getElementById('e-color').value = f.color;
  document.getElementById('ep').value = f.project_id||'';
  document.querySelectorAll('#ec-row .cswatch').forEach(s=>s.classList.toggle('sel', s.dataset.color===f.color));
  document.getElementById('edit-modal').classList.add('open');
}

function deleteFolder(id, name, count){
  document.getElementById('del-form').action = '/document-folders/'+id;
  document.getElementById('del-msg').innerHTML = count>0
    ? `<strong>"${name}"</strong> has <strong>${count} document(s)</strong>. They will be unassigned (not deleted).`
    : `<strong>"${name}"</strong> will be permanently removed.`;
  document.getElementById('del-modal').classList.add('open');
}
</script>
@endsection
