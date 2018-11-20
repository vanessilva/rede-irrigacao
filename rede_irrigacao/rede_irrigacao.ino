#include <DHT.h>


#define DHTPIN 23
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600);
  dht.begin();
}

void loop() {
  // put your main code here, to run repeatedly:  
  int leituraUmidade = dht.readHumidity();  
  int leituraTemperatura = dht.readTemperature();
  Serial.print("Umidade: ");
  Serial.print(leituraUmidade);
  Serial.print("% / Temperatura: ");
  Serial.println(leituraTemperatura);
  delay(1000);
}
