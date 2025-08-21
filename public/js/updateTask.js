
function updateTask(id)  {
    fetch(`/update/task/${id}`,{
        method: 'PUT',
        body: JSON.stringify({
            'title': document.getElementById('task_title').value,
            'description': document.getElementById('task_description').value,
            'status': document.getElementById('task_status').value,
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
        if (body.taskUpdated) {
            document.getElementById('result').innerHTML = creatTableBilanUpdate(body.taskUpdated, body.oldTaskData, body.message);
            document.getElementById('Updated_At').innerHTML = body.taskUpdated.updatedAt;
        }
    })
    .catch(error => {
        document.getElementById('result').innerText = `Erreur: ${error}`;
        console.error(error);
    });

};

function creatTableBilanUpdate(taskUpdated, oldTaskData, message){
    let table = `
    <table class='styled-table'>
        <caption>${message}Bilan des modifications</caption>
        <thead>
            <tr>
                <th>Version</th>
                <th>Titre</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ancienne version</td>
                <td>${oldTaskData.title}</td>
                <td>${oldTaskData.status}</td>
                <td>
                    <p>${oldTaskData.description}</p>
                </td>
            </tr>
            <tr>
                <td>Nouvelle version</td>
                <td>${taskUpdated.title}</td>
                <td>${taskUpdated.status}</td>
                <td>
                    <p>${taskUpdated.description}</p>
                </td>
            </tr>
        </tbody>
    </table>`;

    return table;
}
