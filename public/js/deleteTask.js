function deleteTask(id) {
    fetch(`/delete/${id}/task/`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(response => {
        if (!response.ok) {
            document.getElementById('result').innerText = `Problème - code d'état HTTP : ${response.status}`;
        }
        return response.json();
    })
    .then(body => {
        console.log('body : ', body);
        if (body.delete) {
            document.getElementById(`task_${id}`).remove();
        }
        document.getElementById('result').innerText = body.message;
    })
    .catch(error => {
        document.getElementById('result').innerText = `Erreur: ${error}`;
        console.error(error);
    });
}
