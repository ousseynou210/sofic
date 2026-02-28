@extends('admin.layouts.app')
@section('title','Nouvelle facture')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Nouvelle facture</h1>
            <p class="text-muted mb-0">Creation rapide avec calcul automatique du total</p>
        </div>
        <a class="btn btn-light" href="{{ route('admin.factures.index') }}">Retour</a>
    </div>

    <form
        method="POST"
        action="{{ route('admin.factures.store') }}"
        x-data="factureForm()"
        x-init="init()"
        @submit="startLoading()"
    >
        @csrf
        <div class="card soft-card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Numero facture</label>
                        <input
                            type="text"
                            class="form-control @error('numero_facture') is-invalid @enderror"
                            name="numero_facture"
                            value="{{ old('numero_facture') }}"
                            placeholder="FAC-2026-000001"
                            required>
                        @error('numero_facture')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Client</label>
                        <select class="form-select @error('client_id') is-invalid @enderror" name="client_id" required>
                            <option value="">Choisir...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id')==$client->id)>{{ $client->nom }}</option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date emission</label>
                        <input type="date" class="form-control @error('date_emission') is-invalid @enderror" name="date_emission" value="{{ old('date_emission', now()->format('Y-m-d')) }}" required>
                        @error('date_emission')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date echeance</label>
                        <input type="date" class="form-control @error('date_echeance') is-invalid @enderror" name="date_echeance" value="{{ old('date_echeance') }}">
                        @error('date_echeance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Commercial</label>
                        <select class="form-select @error('commercial_id') is-invalid @enderror" name="commercial_id">
                            <option value="">Aucun</option>
                            @foreach($commerciaux as $item)
                                <option value="{{ $item->id }}" @selected(old('commercial_id')==$item->id)>{{ $item->nom }}</option>
                            @endforeach
                        </select>
                        @error('commercial_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Point de vente</label>
                        <select class="form-select @error('point_vente_id') is-invalid @enderror" name="point_vente_id">
                            <option value="">Aucun</option>
                            @foreach($pointsVente as $item)
                                <option value="{{ $item->id }}" @selected(old('point_vente_id')==$item->id)>{{ $item->nom }}</option>
                            @endforeach
                        </select>
                        @error('point_vente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card soft-card mb-3">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Lignes produits</h6>
                <button type="button" class="btn btn-light btn-sm" @click="addLine()">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter ligne
                </button>
            </div>
            <div class="card-body">
                <template x-for="(line, idx) in lines" :key="idx">
                    <div class="row g-2 align-items-end mb-3 pb-3 border-bottom">
                        <div class="col-lg-3">
                            <label class="form-label">Produit</label>
                            <select class="form-select" :name="`lignes[${idx}][produit_id]`" x-model="line.produit_id" @change="applyProductPrice(line)">
                                <option value="">Libre</option>
                                @foreach($produits as $produit)
                                    <option value="{{ $produit->id }}" data-prix="{{ $produit->prix_vente }}">{{ $produit->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Description</label>
                            <input class="form-control" :name="`lignes[${idx}][description]`" x-model="line.description">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Quantite</label>
                            <input type="number" min="1" class="form-control" :name="`lignes[${idx}][quantite]`" x-model.number="line.quantite">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Prix unitaire</label>
                            <input type="number" min="0" step="0.01" class="form-control" :name="`lignes[${idx}][prix_unitaire]`" x-model.number="line.prix_unitaire">
                        </div>
                        <div class="col-lg-1 text-end">
                            <span class="small text-muted d-block">Total</span>
                            <strong x-text="formatMoney(lineTotal(line))"></strong>
                        </div>
                        <div class="col-lg-1 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm" @click="removeLine(idx)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <div class="d-flex justify-content-end">
                    <h5 class="mb-0">Total facture: <span class="text-primary" x-text="formatMoney(grandTotal())"></span></h5>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
                <span x-show="!isSubmitting"><i class="bi bi-check2-circle me-1"></i>Enregistrer la facture</span>
                <span x-show="isSubmitting"><span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...</span>
            </button>
            <a class="btn btn-light" href="{{ route('admin.factures.index') }}">Annuler</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    @php
        $productsJs = [];
        foreach ($produits as $produit) {
            $productsJs[] = [
                'id' => (string) $produit->id,
                'prix' => (float) $produit->prix_vente,
            ];
        }

        $lignesInitiales = old('lignes', [[
            'produit_id' => '',
            'description' => '',
            'quantite' => 1,
            'prix_unitaire' => 0,
        ]]);
    @endphp

    function factureForm() {
        return {
            isSubmitting: false,
            lines: [],
            products: @json($productsJs),
            init() {
                this.lines = @json($lignesInitiales);
                if (!this.lines.length) this.addLine();
            },
            startLoading() { this.isSubmitting = true; },
            addLine() {
                this.lines.push({ produit_id: '', description: '', quantite: 1, prix_unitaire: 0 });
            },
            removeLine(index) {
                this.lines.splice(index, 1);
                if (!this.lines.length) this.addLine();
            },
            applyProductPrice(line) {
                const product = this.products.find(p => p.id === String(line.produit_id));
                if (product) line.prix_unitaire = product.prix;
            },
            lineTotal(line) {
                return (Number(line.quantite || 0) * Number(line.prix_unitaire || 0));
            },
            grandTotal() {
                return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
            },
            formatMoney(value) {
                return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' FCFA';
            }
        }
    }
</script>
@endpush
