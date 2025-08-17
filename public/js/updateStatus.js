function updateStatus(id, status) {
    
    fetch(`/update/status`, {
        method: 'POST',
        body: JSON.stringify({'id': id, 'status': status}),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',//indiquer au serveur que la requête est faite avec un appel AJAX
            'Content-Type': 'application/json',//type d'information envoyé par JavaScript envoie au serveur dans le body
            'Accept': 'application/json',//type d'information dinformation que le serveur retourne au code JavaScript
        },
    })
    .then((response) => {
        if (!response.ok) {
            document.getElementById('result').innerText = `Problème - code d'état HTTP :  ${response.status}`;
        }
        console.log('response : ', response);
        return response.json();

    }).then((body) => {
        console.log('body : ', body);
        document.getElementById('result').innerText = body.message;
        document.getElementById(`task_${id}`).children[4] = body.message;
        

    })
    .catch(error => document.getElementById('result').innerText = `Erreur: ${error}`);
}
