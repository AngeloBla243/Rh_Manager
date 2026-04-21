{{-- resources/views/admin/employes/partials/documents.blade.php
     Utilisé dans show.blade.php via @include('admin.employes.partials.documents')
--}}

<div class="card mb-4" id="section-documents">
    <div class="card-header">
        <div>
            <div class="card-title">Documents administratifs</div>
            <div class="section-sub">
                {{ $employe->documents->count() }} document(s) ·
                @php $manquants = $employe->documentsManquants(); @endphp
                @if (count($manquants) > 0)
                    <span style="color:var(--amber)">{{ count($manquants) }} manquant(s)</span>
                @else
                    <span style="color:var(--emerald)">Dossier complet ✓</span>
                @endif
            </div>
        </div>
        <button class="btn btn-sm btn-primary" onclick="document.getElementById('modal-doc').style.display='flex'">
            + Ajouter un document
        </button>
    </div>

    {{-- Alerte documents manquants --}}
    @if (count($manquants) > 0)
        <div style="padding:10px 18px;background:var(--amber-bg);border-bottom:1px solid var(--border)">
            <div style="font-size:12px;color:var(--amber);font-weight:500">Documents manquants :</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px">
                @foreach ($manquants as $m)
                    <span class="badge badge-amber">{{ $m }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Liste des documents --}}
    @if ($employe->documents->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Fichier</th>
                        <th>Taille</th>
                        <th>Expiration</th>
                        <th>Ajouté le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employe->documents()->orderBy('type_document')->get() as $doc)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <span style="font-size:18px">{{ $doc->icone }}</span>
                                    <strong>{{ $doc->type_document }}</strong>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <span
                                        style="font-family:var(--mono);font-size:10px;font-weight:700;
                               background:var(--bg2);padding:2px 6px;border-radius:4px;
                               text-transform:uppercase">
                                        {{ $doc->extension_maj }}
                                    </span>
                                    <span class="text-sm"
                                        style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                        title="{{ $doc->nom_fichier }}">
                                        {{ $doc->nom_fichier }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-sm text-muted font-mono">{{ $doc->taille_formatee }}</td>
                            <td class="text-sm">
                                @if ($doc->date_expiration)
                                    <span
                                        style="color:{{ $doc->est_expire ? 'var(--crimson)' : ($doc->expire_bientot ? 'var(--amber)' : 'var(--ink2)') }}">
                                        {{ $doc->date_expiration->format('d/m/Y') }}
                                        @if ($doc->est_expire)
                                            <span class="badge badge-red" style="font-size:9px">Expiré</span>
                                        @elseif($doc->expire_bientot)
                                            <span class="badge badge-amber" style="font-size:9px">Bientôt</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-sm text-muted">{{ $doc->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="table-actions">
                                    @if ($doc->est_previewable)
                                        <a href="{{ route('admin.employes.documents.preview', $doc) }}"
                                            class="btn btn-sm" target="_blank" title="Prévisualiser">👁</a>
                                    @endif
                                    <a href="{{ route('admin.employes.documents.download', $doc) }}" class="btn btn-sm"
                                        title="Télécharger">↓</a>
                                    <form method="POST"
                                        action="{{ route('admin.employes.documents.destroy', $doc) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Supprimer ce document ?')"
                                            title="Supprimer">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty" style="padding:28px">
            <div class="empty-icon">📁</div>
            <div class="empty-title">Aucun document</div>
            <div class="empty-sub">Ajoutez les pièces administratives de l'employé.</div>
        </div>
    @endif
</div>

{{-- Modal : ajouter document --}}
<div class="modal-overlay" id="modal-doc" style="display:none">
    <div class="modal modal-sm">
        <div class="modal-header">
            <span class="modal-title">Ajouter un document</span>
            <button class="modal-close" onclick="document.getElementById('modal-doc').style.display='none'">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.employes.documents.store', $employe) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Type de document <span class="required">*</span></label>
                    <select name="type_document" class="form-control" required>
                        @foreach (json_decode(\App\Models\Parametre::valeur('types_documents', '[]'), true) as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                        <option value="autre">Autre (préciser ci-dessous)</option>
                    </select>
                </div>
                <div class="form-group" id="zone-autre" style="display:none">
                    <label class="form-label">Préciser le type</label>
                    <input type="text" name="type_document_autre" class="form-control"
                        placeholder="Ex : Casier judiciaire">
                </div>
                <div class="form-group">
                    <label class="form-label">Fichier <span class="required">*</span></label>
                    <input type="file" name="fichier" class="form-control" required
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                    <div class="text-xs text-muted mt-1">PDF, image, Word, Excel — max 10 Mo</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Date d'expiration <small class="text-muted">(optionnel)</small></label>
                    <input type="date" name="date_expiration" class="form-control">
                    <div class="text-xs text-muted mt-1">Laissez vide si le document n'expire pas</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn"
                    onclick="document.getElementById('modal-doc').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        document.querySelector('[name="type_document"]')?.addEventListener('change', function() {
            document.getElementById('zone-autre').style.display = this.value === 'autre' ? '' : 'none';
        });
    </script>
@endpush
