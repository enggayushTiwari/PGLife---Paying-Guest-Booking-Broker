from flask import Flask, request, jsonify, render_template
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.application import MIMEApplication
from datetime import datetime
import os
import json

template_dir = os.path.abspath('templates')
app = Flask(__name__, template_folder=template_dir)

@app.route('/')
def index():
    amount = request.args.get('amount', '')
    property_name = request.args.get('property_name', '')
    address = request.args.get('address', '')
    return render_template('indexs.html', amount=amount, property_name=property_name, address=address)

@app.route('/send_email', methods=['POST'])
def send_email_route():
    try:
        data = json.loads(request.form['data'])
        pdf_file = request.files['pdf']

        full_name = data['fullName']
        email = data['email']
        amount = data['amount']
        property_name = data['propertyName']
        property_address = data['propertyAddress']
        payment_id = data['paymentId']

        send_email(full_name, email, amount, property_name, property_address, payment_id, pdf_file)
        return jsonify({"success": True, "message": "Email sent successfully."})
    except Exception as e:
        return jsonify({"success": False, "message": f"Error sending email: {str(e)}"})

def send_email(full_name, email, amount, property_name, property_address, payment_id, pdf_file):
    sender_email = ""
    sender_password = ""
    receiver_email = email

    msg = MIMEMultipart()
    msg['From'] = sender_email
    msg['To'] = receiver_email
    msg['Subject'] = 'PG Life - Booking Confirmation and Receipt'

    body = f"""
    Dear {full_name},

    Thank you for booking with PG Life! Your booking details are as follows:

    Property Details:
    - Property Name: {property_name}
    - Address: {property_address}

    Payment Details:
    - Amount Paid: ₹{amount}
    - Payment ID: {payment_id}
    - Payment Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}
    - Payment Status: Successful

    Please find your payment receipt attached to this email.

    If you have any questions, please don't hesitate to contact us.

    Best Regards,
    PG Life Team
    """
    msg.attach(MIMEText(body, 'plain'))

    # Attach PDF
    pdf_attachment = MIMEApplication(pdf_file.read(), _subtype="pdf")
    pdf_attachment.add_header('Content-Disposition', 'attachment', filename=f'PGLife_Receipt_{payment_id}.pdf')
    msg.attach(pdf_attachment)

    try:
        server = smtplib.SMTP('smtp.gmail.com', 587)
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
