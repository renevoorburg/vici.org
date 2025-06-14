
function validateRegistration() 
//
{
    allFine=true;
    errorMessage="";
    
    givenUsername=document.getElementById("frm_name").value.replace(/^\s+|\s+$/g,"").replace(/\s{2,}/g," ");
    document.getElementById("frm_name").value=givenUsername;
    if (givenUsername.length < 4 ) {
        errorMessage+=lng_err_username_too_short+"\n";
    };
    
    givenPassword=document.getElementById("frm_password").value;
    if (givenPassword!=document.getElementById("frm_passwordrepeat").value) {
        errorMessage+=lng_err_passwords_dont_match+"\n";
    } else {
        if (givenPassword.length < 7 ) { errorMessage+=lng_err_password_too_short+"\n"; };
        if (!(givenPassword.match(/\d/))) { errorMessage += lng_err_password_needs_number+"\n"; }
        if (!(givenPassword.match(/[A-Z]/))) { errorMessage += lng_err_password_needs_uppercase+"\n"; }
        if (!(givenPassword.match(/[a-z]/))) { errorMessage += lng_err_password_needs_lowercase+"\n"; }
    };
    
    givenRealname=document.getElementById("frm_realname").value.replace(/^\s+|\s+$/g,"").replace(/\s{2,}/g," ");
    document.getElementById("frm_realname").value=givenRealname;
    if (givenRealname.length < 4 ) {
        errorMessage+=lng_err_fullname_too_short+"\n";
    }
    
    givenEmail=document.getElementById("frm_email").value.replace(/\s/g,"");
    document.getElementById("frm_email").value=givenEmail;
    var atpos=givenEmail.indexOf("@");
    var dotpos=givenEmail.lastIndexOf(".");
    if (atpos<1 || dotpos<atpos+2 || dotpos+2>=givenEmail.length) {
        errorMessage+=lng_err_wrong_email+"\n";
    }
    
    if(errorMessage.length > 1) {
        alert(errorMessage);
        return(false);
    }
    return (true);
};

function validateEmail() 
// TODO
// should be called from previous function
{

    allFine=true;
    errorMessage="";

    givenEmail=document.getElementById("frm_email").value.replace(/\s/g,"");
    document.getElementById("frm_email").value=givenEmail;
    var atpos=givenEmail.indexOf("@");
    var dotpos=givenEmail.lastIndexOf(".");
    if (atpos<1 || dotpos<atpos+2 || dotpos+2>=givenEmail.length) {
        errorMessage+=lng_err_wrong_email+"\n";
    }
    
    if(errorMessage.length > 1) {
        alert(errorMessage);
        return(false);
    }
    return (true);
};


function validatePassword() 
// TODO
// should be called from a previous function
{

    allFine=true;
    errorMessage="";

    givenPassword=document.getElementById("frm_password").value;
    if (givenPassword!=document.getElementById("frm_passwordrepeat").value) {
        errorMessage+=lng_err_passwords_dont_match+"\n";
    } else {
        if (givenPassword.length < 7 ) { errorMessage+=lng_err_password_too_short+"\n"; };
        if (!(givenPassword.match(/\d/))) { errorMessage += lng_err_password_needs_number+"\n"; }
        if (!(givenPassword.match(/[A-Z]/))) { errorMessage += lng_err_password_needs_uppercase+"\n"; }
        if (!(givenPassword.match(/[a-z]/))) { errorMessage += lng_err_password_needs_lowercase+"\n"; }
    };
    
    if(errorMessage.length > 1) {
        alert(errorMessage);
        return(false);
    }
    return (true);
};


function initialize() 
// 
{

  
};
