<?xml version="1.0" encoding="UTF-8"?>
<definitions name="SIPUS" targetNamespace="http://sipus-del.test/soap"
 xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:tns="http://sipus-del.test/soap"
 xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
 <message name="ClearanceRequest"><part name="nim_peminjam" type="xsd:string"/></message>
 <message name="ClearanceResponse"><part name="active_loans" type="xsd:int"/><part name="is_cleared" type="xsd:boolean"/></message>
 <portType name="SipusPortType"><operation name="CheckClearance"><input message="tns:ClearanceRequest"/><output message="tns:ClearanceResponse"/></operation></portType>
 <binding name="SipusBinding" type="tns:SipusPortType"><soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/><operation name="CheckClearance"><soap:operation soapAction="CheckClearance"/><input><soap:body use="literal"/></input><output><soap:body use="literal"/></output></operation></binding>
 <service name="SipusService"><port name="SipusPort" binding="tns:SipusBinding"><soap:address location="{{ url('/soap/sipus') }}"/></port></service>
</definitions>
