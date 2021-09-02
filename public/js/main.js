'use struct';

const mainApi = "/api/";
const request = new XMLHttpRequest();
let error;
let pagination;

const axios = {
  GET: function (params, cb) {
    request.onreadystatechange = cb;
    request.open('GET', mainApi + params.url);
    request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    request.send();
  },
  POST: function (params, cb) {
    request.onreadystatechange = cb;
    request.open('POST', mainApi + params.url);
    request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    request.send(JSON.stringify(params.body));
  },
  DELETE: function (params, cb) {
    request.onreadystatechange = cb;
    request.open('DELETE', mainApi + params.url);
    request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    request.send();
  }
}

const cbError = function() {
  if (request.readyState == 4) {
    const response = JSON.parse(request.responseText);
    if(request.status !== 200) { 
      error.innerHTML = response.mes;
      error.hidden = false;
    }
    if (response.err == "error") {
      error.innerHTML = response.mes;
      error.hidden = false;
    }
  }

}

const onLogin = function() {
  const login = document.getElementById("login").value;
  const passwd = document.getElementById("password").value;
  axios.POST({ url: "auth", body: {login, passwd} }, () => {
    cbError()
    if (request.readyState == 4) {
      const response = JSON.parse(request.responseText);
      if(request.status !== 200) { 
        error.innerHTML = response.mes;
        error.hidden = false;
      }
      if (response.err == "ok") {
        window.location.reload();
      }
    }
  })
}
const onRegister = function() {
  const login = document.getElementById("login").value;
  const passwd = document.getElementById("password").value;
  axios.POST({ url: "registration", body: {login, passwd} }, cbError)
}

const onLogout = function() {
  axios.DELETE({ url: "auth" }, () => {
    cbError()
    if (request.readyState == 4) {
      window.location.reload();
    }
  })
}

const viewTableRow = function (row) {
  let rowText = '<tr>';
  rowText += `<td><div class="info_user"><p>${row['login']}</p><p>${row['name']}</p></div></td>`;
  rowText += `<td>${row['group_name']}</td>`;
  rowText += '</tr>';
  return rowText;
}
const viewTable = function (data) {
  let tableText = '<table>';
  for (const row of data) {
    tableText += viewTableRow(row);
  }
  tableText += '</table>';
  return tableText;
}

const viewPagination = function (maxPage = 1) {
  let pagination = '<div id="pagination" page="1">';
  for (i = 1; i <= maxPage; i++) {
    pagination += `<a href="#!${i}" class="pagination_i" onclick="nextPage(this)">${i}</a>`;
  }
  const next = "next";
  pagination += `<a href="#!+1" class="pagination_i" onclick="nextPage(this)">Next</a></div>`;
  return pagination;
}

const onGetStudent = function(page = 1) {
  axios.GET({ url: "users?" + "page=" + page }, () => {
    cbError()
    if (request.readyState == 4) {
      const response = JSON.parse(request.responseText);
      const studentList = response.list;
      const maxPage = response.maxPage;

      table.innerHTML = viewTable(studentList);
      if (!document.querySelector("#pagination")) {
        paginate.innerHTML = viewPagination(maxPage);
      }
      
    }
  })
}

const nextPage = function (element) {
  const href = element.attributes.href.value;
  const actpage = document.querySelector("#pagination");
  let page;
  if (href == "#!+1") {
    page = +actpage.attributes.page.value + 1
  } else {
    page = +href.replace("#!", "")
  }
  if(actpage) {
    actpage.setAttribute("page", page)
  }
  onGetStudent(page);
}


function main() {
  error = document.getElementById("error");
  pagination = document.getElementById("pagination");
  document.getElementById("submitLogin") && document.getElementById("submitLogin").addEventListener("click", onLogin);
  document.getElementById("submitReg") && document.getElementById("submitReg").addEventListener("click", onRegister);
  document.getElementById("logout") && document.getElementById("logout").addEventListener("click", onLogout);
};

main();
