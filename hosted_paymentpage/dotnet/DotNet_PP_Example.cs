using System;
using System.IO;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Security.Cryptography.X509Certificates;
using ngapidll;

namespace DotnetPP_app01
{
    class Program
    {
        static void Main(string[] args)
        {


            bool tru = true;
            bool fls = false;
            string merchcert = "C:\\Visual Studio 2010\\Projects\\DotnetPP_app01\\DotnetPP_app01\\test_keys\\merchant.pfx";
            string drwpcert = "C:\\Visual Studio 2010\\Projects\\DotnetPP_app01\\DotnetPP_app01\\test_keys\\netgiro.cer";

            string head = "https://testpage.payments.digitalriver.com/pay/?creq="; // "https://secure.payments.digitalriver.com/pay/?creq=";

            PPDOTNETPaymentPageHandler paymentPageHandler = new PPDOTNETPaymentPageHandler();

            X509Certificate2 certmerch = (X509Certificate2)PPDOTNETSecurityHandler.getCert( merchcert, "merchant", ref tru);
            X509Certificate2 certdrwp = new X509Certificate2(PPDOTNETSecurityHandler.getCert( drwpcert, "", ref fls));
            
            paymentPageHandler.init(certmerch, certdrwp, head);

            PPDOTNETCustomerRedirect request = BuildRequest();
            string redirectUrl = paymentPageHandler.createPaymentPageUrl(request);
            System.Console.Out.WriteLine(redirectUrl);
                  
            // sendRedirect(redirectUrl); // somehow display the URL in a browser. This is not implemented here
            // get back a response when consumer comes back 

            string respString = "BEii9iVIovcIXe2rbjt31F7LixHliaguahcPN-YJF9HFzln_NDbJgRBp01pLjIy3su5FjzW-rPXO0hpAwhL6-fI2PjTxQ0posvZqX3PVQiAwWvWgGTFTMnbItbo_V6fQBJrZDRF_TdyH92KMP4RHj8yACK8F_Zm_r43-stm1w6K6dNBx4JXCG0BWlSY9ToFSU6gngfEdiYa__d6Yid9w2YwgSDWUN-3qRy83uJ3LhGhF5dETLuRa0iW9_gveowjtq8lvksn1LwF2PSHFA5p94XPGaepjbZMly0BB8EKU0MeLHdtBIhiK4kJseeyPEFif34zXOyFdqmci-Yh3LsGMwHJKVe5aQWDAua81YZBpXMA4PlYa_aFvUp3UC3oA9RI8ZGQNkqTiCbJJce_iSg1by9ZX6AtEuOmDk8WkRd4EUeZuEXBh3sYB6p3CyE_ESOCvXAseu8GsksTZAhIToRUeJ3SCkNy3BO9f9pTpfFieBl9JTGFjjlmt1DoaTO9JZ4B7wvmNfY7Z9CNVQmHyyl7zGtz9G1ldlEt9d80LxwreGLf-EMrevfpUQb6jmEH1QqsUp2ExNiu4qQJYobs8tMw-OJkWnjtcSLL8-g==";
            PPDOTNETPaymentPageResponse response = paymentPageHandler.unpackResponse(respString);
            System.Console.Out.WriteLine("Response: mid=" + response.Mid + ", orderid="
                + response.OrderId + ", status=" + response.Status);

        
        }

        private static PPDOTNETCustomerRedirect BuildRequest()
        {
            PPDOTNETCustomerRedirect redirect = new PPDOTNETCustomerRedirect();
            redirect.Mid = 1616454044L;
            redirect.PosId = "0";
            redirect.TransactionChannel = "Web Online";
            redirect.TransactionType = "authorize";
            redirect.OrderId = "orderid-from-NEt-004";
            redirect.Amount = 123.0;
            redirect.Currency = "EUR";
            redirect.CustomerCountry = "GB";
            redirect.CustomerLanguage = "en";
            // redirect.PaymentMethodId = 29;
            redirect.RedirectReturnUrl = "http://localhost/drwp/result_def.php?no=1"; 
            return redirect;
        }
    }
}
