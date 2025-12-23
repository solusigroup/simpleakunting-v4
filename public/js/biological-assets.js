// Biological Assets Management JavaScript

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Aset Biologis';
    document.getElementById('assetForm').reset();
    document.getElementById('assetId').value = '';
    document.getElementById('code').disabled = false;
    document.getElementById('acquisition_date').disabled = false;
    document.getElementById('acquisition_cost').disabled = false;
    document.getElementById('activeToggle').classList.add('hidden');
    toggleFairValueFields();
    document.getElementById('assetModal').classList.remove('hidden');
}

function editAsset(asset) {
    document.getElementById('modalTitle').textContent = 'Edit Aset Biologis';
    document.getElementById('assetId').value = asset.id;
    document.getElementById('name').value = asset.name;
    document.getElementById('maturity_status').value = asset.maturity_status;
    document.getElementById('location').value = asset.location || '';
    document.getElementById('notes').value = asset.notes || '';
    document.getElementById('is_active').checked = asset.is_active;
    document.getElementById('activeToggle').classList.remove('hidden');
    
    // Disable fields that can't be edited
    document.getElementById('code').disabled = true;
    document.getElementById('category').disabled = true;
    document.getElementById('asset_type').disabled = true;
    document.getElementById('quantity').disabled = true;
    document.getElementById('unit').disabled = true;
    document.getElementById('acquisition_date').disabled = true;
    document.getElementById('acquisition_cost').disabled = true;
    document.getElementById('valuation_method').disabled = true;
    document.getElementById('current_fair_value').disabled = true;
    document.getElementById('cost_to_sell').disabled = true;
    document.getElementById('coa_id').disabled = true;
    document.getElementById('fair_value_gain_loss_coa_id').disabled = true;
    
    document.getElementById('assetModal').classList.remove('hidden');
}

function viewAsset(id) {
    window.location.href = `/biological-assets/${id}`;
}

function closeModal() {
    document.getElementById('assetModal').classList.add('hidden');
    document.getElementById('valuationModal')?.classList.add('hidden');
    document.getElementById('transformationModal')?.classList.add('hidden');
    document.getElementById('harvestModal')?.classList.add('hidden');
}

function toggleFairValueFields() {
    const method = document.getElementById('valuation_method').value;
    const fairValueFields = document.getElementById('fairValueFields');
    
    if (method === 'fair_value') {
        fairValueFields.classList.remove('hidden');
        document.getElementById('current_fair_value').required = true;
    } else {
        fairValueFields.classList.add('hidden');
        document.getElementById('current_fair_value').required = false;
    }
}

// Main form submission
document.getElementById('assetForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('assetId').value;
    const isEdit = !!id;
    
    const data = isEdit ? {
        name: document.getElementById('name').value,
        maturity_status: document.getElementById('maturity_status').value,
        location: document.getElementById('location').value,
        notes: document.getElementById('notes').value,
        is_active: document.getElementById('is_active').checked,
    } : {
        code: document.getElementById('code').value,
        name: document.getElementById('name').value,
        category: document.getElementById('category').value,
        asset_type: document.getElementById('asset_type').value,
        maturity_status: document.getElementById('maturity_status').value,
        quantity: parseFloat(document.getElementById('quantity').value),
        unit: document.getElementById('unit').value,
        acquisition_date: document.getElementById('acquisition_date').value,
        acquisition_cost: parseFloat(document.getElementById('acquisition_cost').value),
        valuation_method: document.getElementById('valuation_method').value,
        current_fair_value: document.getElementById('current_fair_value').value ? parseFloat(document.getElementById('current_fair_value').value) : null,
        cost_to_sell: parseFloat(document.getElementById('cost_to_sell').value || 0),
        location: document.getElementById('location').value,
        notes: document.getElementById('notes').value,
        coa_id: parseInt(document.getElementById('coa_id').value),
        fair_value_gain_loss_coa_id: document.getElementById('fair_value_gain_loss_coa_id').value ? parseInt(document.getElementById('fair_value_gain_loss_coa_id').value) : null,
    };

    try {
        const response = await fetch(isEdit ? `/biological-assets/${id}` : '/biological-assets', {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Terjadi kesalahan');
        }
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan saat menyimpan data');
    }
});

// Valuation Modal
function openValuationModal(asset) {
    document.getElementById('valuationAssetId').value = asset.id;
    document.getElementById('valuationAssetName').textContent = asset.name;
    document.getElementById('valuation_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('valuation_current_fair_value').value = asset.current_fair_value || '';
    document.getElementById('valuation_cost_to_sell').value = asset.cost_to_sell || 0;
    document.getElementById('valuationModal').classList.remove('hidden');
}

document.getElementById('valuationForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('valuationAssetId').value;
    const data = {
        valuation_date: document.getElementById('valuation_date').value,
        current_fair_value: parseFloat(document.getElementById('valuation_current_fair_value').value),
        cost_to_sell: parseFloat(document.getElementById('valuation_cost_to_sell').value),
        valuation_method: document.getElementById('valuation_method_input').value,
        valuation_notes: document.getElementById('valuation_notes').value,
        create_journal: document.getElementById('create_valuation_journal').checked,
    };

    try {
        const response = await fetch(`/biological-assets/${id}/valuate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            alert('Penilaian nilai wajar berhasil dicatat');
            location.reload();
        } else {
            alert(result.message || 'Terjadi kesalahan');
        }
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan saat menyimpan penilaian');
    }
});

// Transformation Modal
function openTransformationModal(asset) {
    document.getElementById('transformationAssetId').value = asset.id;
    document.getElementById('transformationAssetName').textContent = asset.name;
    document.getElementById('transformation_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('transformationModal').classList.remove('hidden');
}

document.getElementById('transformationForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('transformationAssetId').value;
    const data = {
        transformation_type: document.getElementById('transformation_type').value,
        transaction_date: document.getElementById('transformation_date').value,
        quantity_change: parseFloat(document.getElementById('quantity_change').value),
        description: document.getElementById('transformation_description').value,
    };

    try {
        const response = await fetch(`/biological-assets/${id}/transform`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            alert('Transformasi biologis berhasil dicatat');
            location.reload();
        } else {
            alert(result.message || 'Terjadi kesalahan');
        }
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan saat menyimpan transformasi');
    }
});

// Harvest Modal
function openHarvestModal(asset) {
    document.getElementById('harvestAssetId').value = asset.id;
    document.getElementById('harvestAssetName').textContent = asset.name;
    document.getElementById('harvest_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('harvest_unit').value = asset.unit;
    document.getElementById('harvestModal').classList.remove('hidden');
}

document.getElementById('harvestForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('harvestAssetId').value;
    const data = {
        harvest_date: document.getElementById('harvest_date').value,
        product_name: document.getElementById('product_name').value,
        quantity: parseFloat(document.getElementById('harvest_quantity').value),
        unit: document.getElementById('harvest_unit').value,
        fair_value_at_harvest: parseFloat(document.getElementById('fair_value_at_harvest').value),
        cost_to_sell: parseFloat(document.getElementById('harvest_cost_to_sell').value),
        coa_id: parseInt(document.getElementById('harvest_coa_id').value),
        notes: document.getElementById('harvest_notes').value,
        create_journal: document.getElementById('create_harvest_journal').checked,
    };

    try {
        const response = await fetch(`/biological-assets/${id}/harvest`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            alert('Panen berhasil dicatat');
            location.reload();
        } else {
            alert(result.message || 'Terjadi kesalahan');
        }
    } catch (error) {
        console.error(error);
        alert('Terjadi kesalahan saat menyimpan panen');
    }
});
