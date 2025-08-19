function creatTask() {
    fetch(`/add/task`, {
        method: 'POST',
        body: JSON.stringify({
            'title': document.getElementById('title').value,
            'description': document.getElementById('description').value,
            'status': document.getElementById('status').value
        }),
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
        //teste si la tâche a été crée
        let taskCreated = body.taskCreated;
        if (taskCreated) {
            document.getElementById('tasks-body').appendChild(creatTr(taskCreated));
            document.getElementById('title').value = "";
            document.getElementById('description').value = "";
            document.getElementById('status').value = "";
            document.getElementById('result').innerHTML = creatTableBilan(taskCreated, body.message);
        }
        else{
            document.getElementById('result').innerHTML = body.message;
        }
        
    })
    .catch(error => {
        //document.getElementById('result').innerText = `Erreur: ${error}`;
        console.error(error);
    });
}

function creatTr(taskCreated){
    let id = taskCreated.id;
    let tr = document.createElement('tr');
    tr.setAttribute('id', 'task_'+id);
    tr.innerHTML = `
        <td>${id}</td>
        <td>${taskCreated.title}</td>
        <td>
            <fieldset>   
                <div>
                    <input 
                        type="radio" id="hp_${id}" name="status_${id}" value="en retard"
                        onchange="updateStatus(${id}, 'en retard')"
                        ${taskCreated.status === 'en retard' ? 'checked' : ''}>
                    <label for="hp_${id}">En retard</label>
                </div>

                <div>
                    <input 
                        type="radio" id="ec_${id}" name="status_${id}" value="en cours"
                        onchange="updateStatus(${id}, 'en cours')"
                        ${taskCreated.status === 'en cours' ? 'checked' : ''}>
                    <label for="ec_${id}">En cours</label>
                </div>

                <div>
                    <input 
                        type="radio" id="ter_${id}" name="status_${id}" value="terminée"
                        onchange="updateStatus(${id}, 'terminée')"
                        ${taskCreated.status === 'terminée' ? 'checked' : ''}>
                    <label for="ter_${id}">Terminée</label>
                </div>
            </fieldset>
        </td>
        <td>${taskCreated.description}</td>
        <td>${taskCreated.createdAt}</td>
        <td>${taskCreated.updatedAt}</td>
        <td><button onclick="deleteTask(${id})">Delete</button></td>
        <td><a href="/update/task/${id}">Update</a></td>`;

        return tr;
}


function creatTableBilan(taskCreated, message){
    let table = `
    <table class='styled-table'>
        <caption>${message}</caption>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Status</th>
                <th>Description</th>
                <th>Creat At</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>${taskCreated.id}</td>
                <td>${taskCreated.title}</td>
                <td>${taskCreated.status}</td>
                <td>${taskCreated.description}</td>
                <td>${taskCreated.createdAt}</td>
            </tr>
        </tbody>
    </table>`;

    return table;
}