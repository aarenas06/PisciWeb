const inputs = document.querySelectorAll(".input");


function addcl(){
	let parent = this.parentNode.parentNode;
	parent.classList.add("focus");
}

function remcl(){
	let parent = this.parentNode.parentNode;
	if(this.value == ""){
		parent.classList.remove("focus");
	}
}


inputs.forEach(input => {
	input.addEventListener("focus", addcl);
	input.addEventListener("blur", remcl);
});

// Toggle password visibility (eye icon)
const togglePassword = document.getElementById('togglePassword');
if (togglePassword) {
	const password = document.getElementById('password');
	togglePassword.addEventListener('click', function () {
		const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
		password.setAttribute('type', type);
		this.classList.toggle('fa-eye');
		this.classList.toggle('fa-eye-slash');
	});
}

// Small helper to post FormData to an API endpoint using apiUrl(path)
async function postFormTo(path, formData) {
	if (typeof apiUrl !== 'function') {
		throw new Error('apiUrl is not defined. Include api-config.js before this file.');
	}
	const req = await fetch(apiUrl(path), { method: 'POST', body: formData });
	return req.text();
}
