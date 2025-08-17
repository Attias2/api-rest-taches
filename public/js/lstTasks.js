async function lstTask() {
    fetch(`/lst/tasks`, {
        method: 'GET',
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

        if (body.dataTasks) {
            document.getElementById('tasks-body').innerHTML = creatTrLst(body.dataTasks);
        }
        document.getElementById('result').innerText = body.message;
    })
    .catch(error => {
        document.getElementById('result').innerText = `Erreur: ${error}`;
        console.error(error);
    });
}


function creatTrLst(dataTasks) {
    let tbody = '';
    
    console.log(dataTasks);
    for (let i = 0; i < dataTasks.length; i += 6) {
        let id = dataTasks[i];       // ID de la tâche
        let status = dataTasks[i+2]; // Statut de la tâche
        tbody += `
            <tr id='task_${id}'>
                <td>${id}</td>
                <td>${dataTasks[i+1]}</td>
                <td>
                    <fieldset>   
                        <div>
                            <input 
                                type="radio" id="hp_${id}" name="status_${id}" value="en retard"
                                onchange="updateStatus(${id}, 'en retard')"
                                ${status === 'en retard' ? 'checked' : ''}>
                            <label for="hp_${id}">En retard</label>
                        </div>

                        <div>
                            <input 
                                type="radio" id="ec_${id}" name="status_${id}" value="en cours"
                                onchange="updateStatus(${id}, 'en cours')"
                                ${status === 'en cours' ? 'checked' : ''}>
                            <label for="ec_${id}">En cours</label>
                        </div>

                        <div>
                            <input 
                                type="radio" id="ter_${id}" name="status_${id}" value="terminée"
                                onchange="updateStatus(${id}, 'terminée')"
                                ${status === 'terminée' ? 'checked' : ''}>
                            <label for="ter_${id}">Terminée</label>
                        </div>
                    </fieldset>
                </td>
                <td>${dataTasks[i+3]}</td>
                <td>${dataTasks[i+4]}</td>
                <td>${dataTasks[i+5]}</td>

                <td><button onclick="deleteTask(${id})">Delete</button></td>
                <td><a href="/update/task/${id}">Update</a></td>
            </tr>`;
    }

    return tbody;
}
        console.log('dataTasks[i], dataTasks[i+1], dataTasks[i+2], dataTasks[i+3], dataTasks[i+4], dataTasks[i+5]');


lstTask();
