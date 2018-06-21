public class UnicodeConvert
{

  public static long UnicodeToUTF8(long unic)
  {
    long utf8 = 0;
    long b[] = new long[4];  //JAVA have no unsigned byte
        
    //init byte array
    for (int i=0 ; i < b.length; i++) b[i]=0;
        
    if ( unic <= 0x0000007F )  
    {  
        // * U-00000000 - U-0000007F:  0xxxxxxx  
        b[0]    = unic & 0x7F; 
        utf8    = b[0];
    }  
    else if ( unic >= 0x00000080 && unic <= 0x000007FF )  
    {  
        // * U-00000080 - U-000007FF:  110xxxxx 10xxxxxx  
        b[0] =   (unic & 0x3F) | 0x80;          //low byte
        b[1] =   ((unic >> 6) & 0x1F) | 0xC0;   //high byte
        utf8 =   b[1]<<8 | b[0];
    }  
    else if ( unic >= 0x00000800 && unic <= 0x0000FFFF )  
    {  
        // * U-00000800 - U-0000FFFF:  1110xxxx 10xxxxxx 10xxxxxx  
        b[0] =  (unic & 0x3F) | 0x80;  
        b[1] =  ((unic >>  6) & 0x3F) | 0x80;  
        b[2] =  ((unic >> 12) & 0x0F) | 0xE0;
        utf8 =  b[2]<<16 |b[1]<<8 | b[0];  
    }  
    else if ( unic >= 0x00010000 && unic <= 0x001FFFFF )  
    {  
        // * U-00010000 - U-001FFFFF:  11110xxx 10xxxxxx 10xxxxxx 10xxxxxx  
        b[0] = (unic & 0x3F) | 0x80;  
        b[1] = ((unic >>  6) & 0x3F) | 0x80;  
        b[2] = ((unic >> 12) & 0x3F) | 0x80;  
        b[3] = ((unic >> 18) & 0x07) | 0xF0;
        utf8 =  b[3]<<24 | b[2]<<16 |b[1]<<8 | b[0];    
    }         
        return utf8;
  }
}
