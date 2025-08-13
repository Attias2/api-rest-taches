function deleteTask(id) {
    if (!confirm('Confirmer la suppression ?')) return;

    fetch(`/delete/task/${id}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erreur:', error));
}
