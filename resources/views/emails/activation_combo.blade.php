<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Mã kích hoạt' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f6fa; font-family:Arial, sans-serif;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="padding:20px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding:20px; background:#4F46E5; color:#ffffff; border-radius:12px 12px 0 0;">
                            <h1 style="margin:0; font-size:24px;">🎉 Chúc mừng bạn đã đăng ký thành công gói {{$package_name}} trên App 3 Gốc!</h1>
                        </td>
                    </tr>
                    <!-- Nội dung -->
                    <tr>
                        <td style="padding:30px;">
                            <h2 style="color:#333; font-size:20px; margin-top:0;">Xin chào {{$name}}</h2>
                            <p style="font-size:16px; color:#555; line-height:1.6;">
                                Cảm ơn bạn đã tin tưởng và đồng hành cùng App 3 Gốc.<br>Dưới đây là mã kích hoạt tài khoản của bạn:
                            </p>
                            <!-- Mã kích hoạt -->
                            @foreach($packages as $item)
                            <h2 style="color:#333; font-size:20px; margin-top:0;">Mã kích hoạt cho gói {{$item['title']}}</h2>
                            <div style="text-align:center; margin:30px 0;">
                                <span style="display:inline-block; background:#4F46E5; color:#ffffff; font-size:28px; font-weight:bold; letter-spacing:3px; padding:15px 25px; border-radius:8px;">
                                    👉 {!! $item['codes'] !!}
                                </span>
                            </div>
                            @endforeach

                            @if(isset($user))
                            <div style="text-align:center; margin:30px 0;">
                                <span style="display:inline-block; background:#4F46E5; color:#ffffff;font-weight:bold; font-size:28px;letter-spacing:3px; padding:5px 25px; border-radius:8px;">
                                    <b>Tài khoản</b>: {{$user->username}}<br>
                                    @if ($isShowDefaultPassword)
                                        <b>Mật khẩu</b>:123456<br>
                                    @endif
                                </span>
                            </div>
                            @endif
                            <!-- Nút kích hoạt (tuỳ chọn nếu có link) -->
                            <p style="font-size:14px; color:#777; text-align:center; margin-top:30px;">
                                Hãy nhập mã này vào ứng dụng để bắt đầu trải nghiệm trọn vẹn các tính năng trong gói {{$package_name}} nhé<br>
                                Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.<br>
                                <br>
                                <br>
                                Thân mến,  <br>
                                Đội ngũ App 3 Gốc <br>
                                © {{ date('Y') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
