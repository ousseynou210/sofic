@extends('admin.layouts.app')
@section('title','Modifier facture')
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Modifier facture {{ $facture->numero_facture }}</h1>
        <a class="btn btn-light" href="{{ route('admin.factures.show', $facture) }}">Retour</a>
    </div>
    <form method="POST" action="{{ route('admin.factures.update', $facture) }}">
        @csrf
        @method('PUT')
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Numero facture</label>
                <input
                    type="text"
                    class="form-control @error('numero_facture') is-invalid @enderror"
                    name="numero_facture"
                    value="{{ old('numero_facture', $facture->numero_facture) }}"
                    required>
                @error('numero_facture')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Client</label>
                <select class="form-select" name="client_id" required>
                    <option value="">Choisir...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id', $facture->client_id)==$client->id)>{{ $client->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Date emission</label><input type="date" class="form-control" name="date_emission" value="{{ old('date_emission', optional($facture->date_emission)->format('Y-m-d')) }}" required></div>
            <div class="col-md-4"><label class="form-label">Date echeance</label><input type="date" class="form-control" name="date_echeance" value="{{ old('date_echeance', optional($facture->date_echeance)->format('Y-m-d')) }}"></div>
            <div class="col-md-4">
                <label class="form-label">Commercial</label>
                <select class="form-select" name="commercial_id">
                    <option value="">Aucun</option>
                    @foreach($commerciaux as $item)
                        <option value="{{ $item->id }}" @selected(old('commercial_id', $facture->commercial_id)==$item->id)>{{ $item->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Point de vente</label>
                <select class="form-select" name="point_vente_id">
                    <option value="">Aucun</option>
                    @foreach($pointsVente as $item)
                        <option value="{{ $item->id }}" @selected(old('point_vente_id', $facture->point_vente_id)==$item->id)>{{ $item->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes', $facture->notes) }}</textarea></div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>Lignes facture</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-line">Ajouter ligne</button>
            </div>
            <div class="card-body">
                <div id="lines-container"></div>
            </div>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2">
            <button class="btn btn-primary">Enregistrer modifications</button>
            <a class="btn btn-secondary" href="{{ route('admin.factures.show', $facture) }}">Retour</a>
        </div>
    </form>

    <template id="line-template">
        <div class="row g-2 align-items-end mb-2 line-item border-bottom pb-2">
            <div class="col-12 col-lg-4">
                <label class="form-label">Produit</label>
                <select class="form-select line-produit" data-name="produit_id">
                    <option value="">Libre</option>
                    @foreach($produits as $produit)
                        <option value="{{ $produit->id }}" data-prix="{{ $produit->prix_vente }}">{{ $produit->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-3"><label class="form-label">Description</label><input class="form-control" data-name="description"></div>
            <div class="col-6 col-lg-2"><label class="form-label">Quantite</label><input type="number" class="form-control" data-name="quantite" value="1" min="1" required></div>
            <div class="col-6 col-lg-2"><label class="form-label">Prix unitaire</label><input type="number" step="0.01" class="form-control" data-name="prix_unitaire" value="0" min="0" required></div>
            <div class="col-12 col-lg-1"><button type="button" class="btn btn-outline-danger w-100 remove-line">X</button></div>
        </div>
    </template>
@endsection

@section('scripts')
<script>
    const container = document.getElementById('lines-container');
    const template = document.getElementById('line-template').content;
    const addBtn = document.getElementById('add-line');
    let index = 0;
    @php
        $lignesFacture = [];
        foreach ($facture->factureLignes as $ligne) {
            $lignesFacture[] = [
                'produit_id' => $ligne->produit_id,
                'description' => $ligne->description,
                'quantite' => $ligne->quantite,
                'prix_unitaire' => (float) $ligne->prix_unitaire,
            ];
        }
        $initialLines = old('lignes', $lignesFacture);
    @endphp
    const initialLines = @json($initialLines);

    function addLine(data = null) {
        const clone = document.importNode(template, true);
        clone.querySelectorAll('[data-name]').forEach((el) => {
            el.name = `lignes[${index}][${el.dataset.name}]`;
        });

        const row = clone.querySelector('.line-item');
        const selectProduit = row.querySelector('select[data-name="produit_id"]');
        const inputDescription = row.querySelector('input[data-name="description"]');
        const inputQuantite = row.querySelector('input[data-name="quantite"]');
        const inputPrix = row.querySelector('input[data-name="prix_unitaire"]');

        if (data) {
            selectProduit.value = data.produit_id ?? '';
            inputDescription.value = data.description ?? '';
            inputQuantite.value = data.quantite ?? 1;
            inputPrix.value = data.prix_unitaire ?? 0;
        }

        selectProduit.addEventListener('change', function () {
            const prix = this.options[this.selectedIndex].dataset.prix;
            if (prix) {
                inputPrix.value = prix;
            }
        });

        row.querySelector('.remove-line').addEventListener('click', function () {
            row.remove();
        });

        container.appendChild(clone);
        index++;
    }

    addBtn.addEventListener('click', () => addLine());
    if (initialLines.length > 0) {
        initialLines.forEach((line) => addLine(line));
    } else {
        addLine();
    }
</script>
@endsection
