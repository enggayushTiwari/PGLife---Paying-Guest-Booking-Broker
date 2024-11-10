from flask import Flask, request, jsonify, render_template
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

app = Flask(__name__, template_folder='.')

@app.route('/')
def index():
    return render_template('payment.html')  # Use the updated form template

@app.route('/send_email', methods=['POST'])
def send_email_route():
    data = request.get_json()

    full_name = data['fullName']
    email = data['email']
    amount = data['amount']

    try:
        send_email(full_name, email, amount)
        return jsonify({"success": True, "message": "Email sent successfully."})
    except Exception as e:
        return jsonify({"success": False, "message": f"Error sending email: {str(e)}"})

def send_email(full_name, email, amount):
    sender_email = "aotiwari_b22@it.vjti.ac.in"  # Replace with your email
    sender_password = "pkyk inji eplu dfri"  # Replace with your app password
    receiver_email = email

    msg = MIMEMultipart()
    msg['From'] = sender_email
    msg['To'] = receiver_email
    msg['Subject'] = 'Payment Confirmation'

    body = f"""
    Hello {full_name},

    Thank you for your payment of ₹{amount}.
    Your payment was successful.

    Regards,
    Your Business Name
    """
    msg.attach(MIMEText(body, 'plain'))

    try:
        server = smtplib.SMTP('smtp.gmail.com', 587)  # Use Gmail's SMTP server
        server.starttls()
        server.login(sender_email, sender_password)
        text = msg.as_string()
        server.sendmail(sender_email, receiver_email, text)
        server.quit()
        print("Email sent successfully.")
    except Exception as e:
        print(f"Failed to send email. Error: {str(e)}")
        raise

if __name__ == '__main__':
    app.run(debug=True)
